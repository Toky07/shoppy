<?php

declare(strict_types=1);

namespace App\Auth\Domain\Repository;

use App\Auth\Domain\Entity\AccountToken;
use App\Auth\Domain\ValueObject\AccountTokenPurpose;
use App\Auth\Domain\ValueObject\TokenHash;
use App\User\Domain\ValueObject\UserId;

interface AccountTokenRepository
{
    public function save(AccountToken $token): void;

    public function findByHash(TokenHash $hash): ?AccountToken;

    public function deleteUnused(UserId $userId, AccountTokenPurpose $purpose): void;

    public function deleteByUserId(UserId $userId): void;
}
