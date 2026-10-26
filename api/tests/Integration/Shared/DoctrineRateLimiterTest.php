<?php

declare(strict_types=1);

use App\Shared\Application\RateLimiter;
use App\Shared\Domain\Exception\RateLimited;
use App\Shared\Infrastructure\Persistence\DoctrineRateLimiter;
use App\Tests\Doubles\FixedClock;
use Doctrine\DBAL\Connection;

function doctrineRateLimiter(Connection $connection, string $now = '2026-08-20T12:00:00+00:00'): DoctrineRateLimiter
{
    return new DoctrineRateLimiter($connection, new FixedClock(new DateTimeImmutable($now)));
}

it('wires the rate limiter port to the shared database adapter', function () {
    expect(self::getContainer()->get(RateLimiter::class))->toBeInstanceOf(DoctrineRateLimiter::class);
});

it('shares one counter so two replicas cannot multiply the allowance', function () {
    $connection = self::getContainer()->get(Connection::class);
    $replicaA = doctrineRateLimiter($connection);
    $replicaB = doctrineRateLimiter($connection);

    $replicaA->hit('POST /auth/login', '203.0.113.8', 2, 60);
    $replicaB->hit('POST /auth/login', '203.0.113.8', 2, 60);

    expect(fn () => $replicaA->hit('POST /auth/login', '203.0.113.8', 2, 60))->toThrow(RateLimited::class);
});

it('forgets hits that fall outside the window', function () {
    $connection = self::getContainer()->get(Connection::class);

    doctrineRateLimiter($connection, '2026-08-20T12:00:00+00:00')->hit('POST /auth/login', '203.0.113.8', 1, 60);
    doctrineRateLimiter($connection, '2026-08-20T12:02:00+00:00')->hit('POST /auth/login', '203.0.113.8', 1, 60);

    expect(true)->toBeTrue();
});
