<?php

declare(strict_types=1);

namespace App\Shared\Application\Event;

final readonly class UserRoleChanged
{
    public function __construct(public string $userId)
    {
    }
}
