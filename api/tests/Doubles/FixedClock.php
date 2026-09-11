<?php

declare(strict_types=1);

namespace App\Tests\Doubles;

use App\Shared\Domain\Clock;
use DateTimeImmutable;

final readonly class FixedClock implements Clock
{
    public function __construct(private DateTimeImmutable $now)
    {
    }

    public function now(): DateTimeImmutable
    {
        return $this->now;
    }
}
