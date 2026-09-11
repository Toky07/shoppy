<?php

declare(strict_types=1);

namespace App\Tests\Doctrine;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Platforms\SQLitePlatform;
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

    /**
     * @deprecated Prefer initialize() + beginTransaction()/rollback() for faster test isolation.
     */
    public static function reset(EntityManagerInterface $entityManager): void
    {
        $entityManager->clear();

        if (!self::hasMappedTables($entityManager)) {
            self::create($entityManager);
            self::$initialized = true;

            return;
        }

        self::truncate($entityManager->getConnection());
    }

    private static function create(EntityManagerInterface $entityManager): void
    {
        $metadata = $entityManager->getMetadataFactory()->getAllMetadata();
        $schemaTool = new SchemaTool($entityManager);
        $schemaTool->dropSchema($metadata);
        $schemaTool->createSchema($metadata);
    }

    private static function hasMappedTables(EntityManagerInterface $entityManager): bool
    {
        $tables = [];

        foreach ($entityManager->getMetadataFactory()->getAllMetadata() as $metadata) {
            $tables[] = $metadata->getTableName();
        }

        if ($tables === []) {
            return false;
        }

        return $entityManager->getConnection()->createSchemaManager()->tablesExist($tables);
    }

    private static function truncate(Connection $connection): void
    {
        $platform = $connection->getDatabasePlatform();
        $tables = $connection->createSchemaManager()->listTableNames();

        if ($tables === []) {
            return;
        }

        $sqlite = $platform instanceof SQLitePlatform;

        if ($sqlite) {
            $connection->executeStatement('PRAGMA foreign_keys = OFF');
        }

        $connection->transactional(function () use ($connection, $platform, $tables): void {
            foreach ($tables as $table) {
                $connection->executeStatement($platform->getTruncateTableSQL($table, true));
            }
        });

        if ($sqlite) {
            try {
                $connection->executeStatement('DELETE FROM sqlite_sequence');
            } catch (Exception) {
            }

            $connection->executeStatement('PRAGMA foreign_keys = ON');
        }
    }
}
