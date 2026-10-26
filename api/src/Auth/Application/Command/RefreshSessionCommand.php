<?php

declare(strict_types=1);

namespace App\Auth\Application\Command;

final readonly class RefreshSessionCommand
{
    public function __construct(public string $refreshToken)
    {
    }
}
