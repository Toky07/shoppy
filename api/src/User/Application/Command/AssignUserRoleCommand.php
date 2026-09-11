<?php

declare(strict_types=1);

namespace App\User\Application\Command;

final readonly class AssignUserRoleCommand
{
    public function __construct(
        public string $id,
        public string $role,
    ) {
    }
}
