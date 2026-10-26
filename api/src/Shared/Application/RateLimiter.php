<?php

declare(strict_types=1);

namespace App\Shared\Application;

interface RateLimiter
{
    public function hit(string $bucket, string $identity, int $limit, int $windowSeconds): void;
}
