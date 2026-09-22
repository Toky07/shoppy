<?php

declare(strict_types=1);

namespace App\Auth\Application\Command;

final readonly class DeleteAccountCommand
{
    public function __construct(
        public string $userId,
        public string $password,
    ) {
    }
}
