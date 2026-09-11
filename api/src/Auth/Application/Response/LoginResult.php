<?php

declare(strict_types=1);

namespace App\Auth\Application\Response;

use App\User\Domain\ValueObject\UserId;

final readonly class LoginResult
{
    public function __construct(
        public UserId $userId,
        public string $accessToken,
    ) {
    }
}
