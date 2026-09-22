<?php

declare(strict_types=1);

namespace App\Auth\Domain\Repository;

use App\Auth\Domain\Entity\Credentials;
use App\User\Domain\ValueObject\UserId;

interface CredentialsRepository
{
    public function save(Credentials $credentials): void;

    public function findByUserId(UserId $userId): ?Credentials;

    public function delete(UserId $userId): void;
}
