<?php

declare(strict_types=1);

namespace App\Shared\Application;

use App\Shared\Domain\Exception\RateLimited;
use Throwable;

final class FailClosedRateLimit
{
    public static function run(callable $work): void
    {
        try {
            $work();
        } catch (RateLimited $exception) {
            throw $exception;
        } catch (Throwable) {
            throw new RateLimited();
        }
    }
}
