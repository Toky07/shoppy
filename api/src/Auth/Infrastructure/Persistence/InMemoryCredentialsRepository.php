<?php

declare(strict_types=1);

namespace App\Auth\Infrastructure\Persistence;

use App\Auth\Domain\Entity\Credentials;
use App\Auth\Domain\Repository\CredentialsRepository;
use App\User\Domain\ValueObject\UserId;

final class InMemoryCredentialsRepository implements CredentialsRepository
{
    /** @var array<string, Credentials> */
    private array $credentials = [];

    public function save(Credentials $credentials): void
    {
        $this->credentials[$credentials->userId()->value()] = $credentials;
    }

    public function findByUserId(UserId $userId): ?Credentials
    {
        return $this->credentials[$userId->value()] ?? null;
    }

    public function delete(UserId $userId): void
    {
        unset($this->credentials[$userId->value()]);
    }
}
