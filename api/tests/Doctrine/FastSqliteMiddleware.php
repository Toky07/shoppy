<?php

declare(strict_types=1);

namespace App\Tests\Doctrine;

use Doctrine\DBAL\Driver;
use Doctrine\DBAL\Driver\Connection;
use Doctrine\DBAL\Driver\Middleware;
use Doctrine\DBAL\Driver\Middleware\AbstractDriverMiddleware;
use SensitiveParameter;

/**
 * The test database is disposable: skipping fsync on every commit keeps the suite fast on slow disks.
 */
final class FastSqliteMiddleware implements Middleware
{
    public function wrap(Driver $driver): Driver
    {
        return new class ($driver) extends AbstractDriverMiddleware {
            public function connect(#[SensitiveParameter] array $params): Connection
            {
                $connection = parent::connect($params);

                if (str_contains((string) ($params['driver'] ?? ''), 'sqlite')) {
                    $connection->exec('PRAGMA synchronous = OFF');
                    $connection->exec('PRAGMA journal_mode = MEMORY');
                }

                return $connection;
            }
        };
    }
}
