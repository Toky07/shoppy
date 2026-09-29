<?php

declare(strict_types=1);

namespace App\Tests\Doctrine;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;

final class DatabaseSchema
{
    private static bool $initialized = false;

    public static function initialize(EntityManagerInterface $entityManager): void
    {
        if (self::$initialized) {
            return;
        }

        self::create($entityManager);
        self::$initialized = true;
    }

    public static function beginTransaction(EntityManagerInterface $entityManager): void
    {
        self::initialize($entityManager);

        $connection = $entityManager->getConnection();

        if (!$connection->isTransactionActive()) {
            $connection->beginTransaction();
        }
    }

    public static function rollback(EntityManagerInterface $entityManager): void
    {
        $connection = $entityManager->getConnection();

        if ($connection->isTransactionActive()) {
            $connection->rollBack();
        }

        $entityManager->clear();
    }

    public static function reset(EntityManagerInterface $entityManager): void
    {
        $entityManager->clear();

        if (!self::$initialized) {
            self::initialize($entityManager);

            return;
        }

        self::truncate($entityManager);
    }

    private static function create(EntityManagerInterface $entityManager): void
    {
        self::ensureDatabaseExists($entityManager->getConnection());

        $metadata = $entityManager->getMetadataFactory()->getAllMetadata();
        $schemaTool = new SchemaTool($entityManager);
        $schemaTool->dropSchema($metadata);
        $schemaTool->createSchema($metadata);
    }

    /**
     * Each Paratest worker gets its own database (see dbname_suffix), created on first use.
     */
    private static function ensureDatabaseExists(Connection $connection): void
    {
        $params = $connection->getParams();
        $name = $params['dbname'] ?? null;

        if (!is_string($name) || $name === '') {
            return;
        }

        unset($params['dbname'], $params['url']);
        $server = DriverManager::getConnection([...$params, 'dbname' => 'postgres']);

        try {
            if (!in_array($name, $server->createSchemaManager()->listDatabases(), true)) {
                $server->createSchemaManager()->createDatabase($server->quoteSingleIdentifier($name));
            }
        } finally {
            $server->close();
        }
    }

    private static function truncate(EntityManagerInterface $entityManager): void
    {
        $connection = $entityManager->getConnection();
        $tables = [];

        foreach ($entityManager->getMetadataFactory()->getAllMetadata() as $metadata) {
            if (!$metadata->isMappedSuperclass && !$metadata->isEmbeddedClass) {
                $tables[] = $connection->quoteSingleIdentifier($metadata->getTableName());
            }
        }

        // DELETE instead of TRUNCATE: TRUNCATE recreates every table file (~400 ms per call on tiny tables).
        $connection->transactional(static function (Connection $connection) use ($tables): void {
            foreach (array_unique($tables) as $table) {
                $connection->executeStatement('DELETE FROM '.$table);
            }
        });
    }
}
