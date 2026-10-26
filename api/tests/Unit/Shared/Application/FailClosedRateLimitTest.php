<?php

declare(strict_types=1);

use App\Shared\Application\FailClosedRateLimit;
use App\Shared\Domain\Exception\RateLimited;

it('turns storage failures into a rate limit', function () {
    expect(fn () => FailClosedRateLimit::run(static function (): void {
        throw new RuntimeException('disk full');
    }))->toThrow(RateLimited::class);
});

it('lets an actual rate limit through', function () {
    expect(fn () => FailClosedRateLimit::run(static function (): void {
        throw new RateLimited();
    }))->toThrow(RateLimited::class);
});

it('returns when storage succeeds', function () {
    $called = false;

    FailClosedRateLimit::run(static function () use (&$called): void {
        $called = true;
    });

    expect($called)->toBeTrue();
});
