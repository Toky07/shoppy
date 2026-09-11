<?php

declare(strict_types=1);

namespace App\Auth\Application\Command;

final readonly class RegisterAccountCommand
{
    public function __construct(
        public string $email,
        public string $password,
    ) {
    }
}
