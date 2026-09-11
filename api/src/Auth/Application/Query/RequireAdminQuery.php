<?php

declare(strict_types=1);

namespace App\Auth\Application\Query;

final readonly class RequireAdminQuery
{
    public function __construct(public string $token)
    {
    }
}
