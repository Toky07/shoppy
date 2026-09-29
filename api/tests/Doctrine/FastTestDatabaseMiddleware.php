<?php

declare(strict_types=1);

namespace App\Tests\Doctrine;

use Doctrine\DBAL\Driver;
use Doctrine\DBAL\Driver\Connection;
use Doctrine\DBAL\Driver\Middleware;
use Doctrine\DBAL\Driver\Middleware\AbstractDriverMiddleware;
use SensitiveParameter;

/**
 * The test database is disposable: not waiting for the WAL flush on every commit keeps the suite fast.
 */
final class FastTestDatabaseMiddleware implements Middleware
{
    public function wrap(Driver $driver): Driver
    {
        return new class ($driver) extends AbstractDriverMiddleware {
            public function connect(#[SensitiveParameter] array $params): Connection
            {
                $connection = parent::connect($params);
                $connection->exec('SET synchronous_commit TO OFF');

                return $connection;
            }
        };
    }
}
