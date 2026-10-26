<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Persistence;

use App\Shared\Application\HealthProbe;
use Doctrine\DBAL\Connection;
use Throwable;

final readonly class DoctrineHealthProbe implements HealthProbe
{
    public function __construct(private Connection $connection)
    {
    }

    public function isHealthy(): bool
    {
        try {
            $this->connection->executeQuery('SELECT 1');

            return true;
        } catch (Throwable) {
            return false;
        }
    }
}
