<?php

declare(strict_types=1);

namespace App\Auth\Application\Command;

final readonly class RequestEmailChangeCommand
{
    public function __construct(
        public string $userId,
        public string $email,
        public string $currentPassword,
    ) {
    }
}
