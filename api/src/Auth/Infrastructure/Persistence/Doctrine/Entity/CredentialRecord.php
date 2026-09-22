<?php

declare(strict_types=1);

namespace App\Auth\Infrastructure\Persistence\Doctrine\Entity;

use App\Auth\Domain\Entity\Credentials;
use App\Auth\Domain\ValueObject\HashedPassword;
use App\User\Domain\ValueObject\UserId;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'credentials')]
class CredentialRecord
{
    #[ORM\Id]
    #[ORM\Column(name: 'user_id', length: 36)]
    private string $userId;

    #[ORM\Column(name: 'password_hash', type: Types::TEXT)]
    private string $passwordHash;

    public static function fromDomain(Credentials $credentials): self
    {
        $record = new self();
        $record->apply($credentials);

        return $record;
    }

    public function toDomain(): Credentials
    {
        return Credentials::create(
            UserId::fromString($this->userId),
            HashedPassword::fromHash($this->passwordHash),
        );
    }

    private function apply(Credentials $credentials): void
    {
        $this->userId = $credentials->userId()->value();
        $this->passwordHash = $credentials->hashedPassword()->value();
    }

    public function updateFromDomain(Credentials $credentials): void
    {
        $this->passwordHash = $credentials->hashedPassword()->value();
    }
}
