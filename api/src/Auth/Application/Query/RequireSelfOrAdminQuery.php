<?php

declare(strict_types=1);

namespace App\Auth\Application\Query;

final readonly class RequireSelfOrAdminQuery
{
    public function __construct(
        public string $token,
        public string $userId,
    ) {
    }
}
