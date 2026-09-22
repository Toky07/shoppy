<?php

declare(strict_types=1);

namespace App\Auth\Domain\Repository;

use App\Auth\Domain\Entity\AccessToken;
use App\Auth\Domain\ValueObject\TokenHash;
use App\User\Domain\ValueObject\UserId;

interface AccessTokenRepository
{
    public function save(AccessToken $token): void;

    public function findByHash(TokenHash $hash): ?AccessToken;

    public function delete(AccessToken $token): void;

    public function deleteByUserId(UserId $userId): void;
}
