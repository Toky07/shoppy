<?php

declare(strict_types=1);

namespace App\Auth\Application\Command;

final readonly class ResetPasswordCommand
{
    public function __construct(
        public string $token,
        public string $password,
    ) {
    }
}
