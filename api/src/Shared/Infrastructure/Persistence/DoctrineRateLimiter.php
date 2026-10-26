<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Persistence;

use App\Shared\Application\FailClosedRateLimit;
use App\Shared\Application\RateLimiter;
use App\Shared\Application\RateLimitWindow;
use App\Shared\Domain\Clock;
use Doctrine\DBAL\Connection;
use RuntimeException;
use Throwable;

final readonly class DoctrineRateLimiter implements RateLimiter
{
    public function __construct(
        private Connection $connection,
        private Clock $clock,
    ) {
    }

    public function hit(string $bucket, string $identity, int $limit, int $windowSeconds): void
    {
        if ($limit <= 0 || $windowSeconds <= 0) {
            return;
        }

        FailClosedRateLimit::run(function () use ($bucket, $identity, $limit, $windowSeconds): void {
            $key = hash('sha256', $bucket.'|'.$identity);
            $now = $this->clock->now()->getTimestamp();
            $startedHere = !$this->connection->isTransactionActive();

            if ($startedHere) {
                $this->connection->beginTransaction();
            }

            try {
                $this->recordHit($key, $now, $now - $windowSeconds, $limit);

                if ($startedHere) {
                    $this->connection->commit();
                }
            } catch (Throwable $exception) {
                if ($startedHere && $this->connection->isTransactionActive()) {
                    $this->connection->rollBack();
                }

                throw $exception;
            }
        });
    }

    private function recordHit(string $key, int $now, int $threshold, int $limit): void
    {
        $this->connection->executeStatement(
            'INSERT INTO rate_limits (id, hits) VALUES (?, ?) ON CONFLICT (id) DO NOTHING',
            [$key, ''],
        );

        $raw = $this->connection->fetchOne(
            'SELECT hits FROM rate_limits WHERE id = ? FOR UPDATE',
            [$key],
        );

        if (!is_string($raw)) {
            throw new RuntimeException('Unable to read the rate limit counter.');
        }

        $window = RateLimitWindow::parse($raw, $threshold)->record($now, $limit);

        $this->connection->executeStatement(
            'UPDATE rate_limits SET hits = ? WHERE id = ?',
            [$window->toString(), $key],
        );
    }
}
