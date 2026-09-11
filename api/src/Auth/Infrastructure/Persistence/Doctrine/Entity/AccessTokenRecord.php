<?php

declare(strict_types=1);

namespace App\Auth\Infrastructure\Persistence\Doctrine\Entity;

use App\Auth\Domain\Entity\AccessToken;
use App\Auth\Domain\ValueObject\TokenHash;
use App\User\Domain\ValueObject\UserId;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'access_tokens')]
class AccessTokenRecord
{
    #[ORM\Id]
    #[ORM\Column(name: 'token_hash', length: 64)]
    private string $tokenHash;

    #[ORM\Column(name: 'user_id', length: 36)]
    private string $userId;

    #[ORM\Column(name: 'expires_at')]
    private DateTimeImmutable $expiresAt;

    #[ORM\Column(name: 'created_at')]
    private DateTimeImmutable $createdAt;

    public static function fromDomain(AccessToken $token): self
    {
        $record = new self();
        $record->apply($token);

        return $record;
    }

    public function toDomain(): AccessToken
    {
        return AccessToken::issue(
            UserId::fromString($this->userId),
            TokenHash::fromHash($this->tokenHash),
            $this->expiresAt,
            $this->createdAt,
        );
    }

    private function apply(AccessToken $token): void
    {
        $this->tokenHash = $token->hash()->value();
        $this->userId = $token->userId()->value();
        $this->expiresAt = $token->expiresAt();
        $this->createdAt = $token->createdAt();
    }
}
