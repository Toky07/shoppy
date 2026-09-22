<?php

declare(strict_types=1);

namespace App\Auth\Application\Command;

final readonly class RequestEmailVerificationCommand
{
    public function __construct(public string $userId)
    {
    }
}
