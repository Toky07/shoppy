<?php

declare(strict_types=1);

use App\Shared\Domain\Exception\RateLimited;
use App\Shared\Infrastructure\Http\FileRateLimiter;
use App\Tests\Doubles\FixedClock;

function fileRateLimiter(string $directory, string $now = '2026-08-20T12:00:00+00:00'): FileRateLimiter
{
    return new FileRateLimiter($directory, new FixedClock(new DateTimeImmutable($now)));
}

function fileRateLimiterDirectory(): string
{
    $directory = sys_get_temp_dir().'/shoppy-rate-limit-'.bin2hex(random_bytes(8));
    mkdir($directory);

    return $directory;
}

it('rejects further hits once the limit is reached', function () {
    $limiter = fileRateLimiter(fileRateLimiterDirectory());

    $limiter->hit('POST /auth/login', '203.0.113.8', 2, 60);
    $limiter->hit('POST /auth/login', '203.0.113.8', 2, 60);

    expect(fn () => $limiter->hit('POST /auth/login', '203.0.113.8', 2, 60))->toThrow(RateLimited::class);
});

it('forgets hits that fall outside the window', function () {
    $directory = fileRateLimiterDirectory();

    fileRateLimiter($directory, '2026-08-20T12:00:00+00:00')->hit('POST /auth/login', '203.0.113.8', 1, 60);
    fileRateLimiter($directory, '2026-08-20T12:02:00+00:00')->hit('POST /auth/login', '203.0.113.8', 1, 60);

    expect(true)->toBeTrue();
});

it('rejects the request when the rate limit directory cannot be created', function () {
    $blocker = sys_get_temp_dir().'/shoppy-rate-limit-file-'.bin2hex(random_bytes(8));
    file_put_contents($blocker, 'not a directory');

    $limiter = fileRateLimiter($blocker.'/nested');

    expect(fn () => $limiter->hit('POST /auth/login', '203.0.113.8', 10, 60))->toThrow(RateLimited::class);
});

it('rejects the request when the rate limit file cannot be opened', function () {
    $directory = fileRateLimiterDirectory();
    mkdir($directory.'/'.hash('sha256', 'POST /auth/login|203.0.113.8'));

    $limiter = fileRateLimiter($directory);

    expect(fn () => $limiter->hit('POST /auth/login', '203.0.113.8', 10, 60))->toThrow(RateLimited::class);
});
