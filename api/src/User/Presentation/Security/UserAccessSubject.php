<?php

declare(strict_types=1);

namespace App\User\Presentation\Security;

final readonly class UserAccessSubject
{
    public function __construct(public string $userId)
    {
    }
}
