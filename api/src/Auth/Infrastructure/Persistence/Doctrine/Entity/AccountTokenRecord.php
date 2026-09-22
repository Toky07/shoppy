<?php

declare(strict_types=1);

namespace App\Auth\Infrastructure\Persistence\Doctrine\Entity;

use App\Auth\Domain\Entity\AccountToken;
use App\Auth\Domain\ValueObject\AccountTokenPurpose;
use App\Auth\Domain\ValueObject\TokenHash;
use App\User\Domain\ValueObject\UserId;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'account_tokens')]
#[ORM\Index(name: 'idx_account_tokens_user_purpose', columns: ['user_id', 'purpose'])]
class AccountTokenRecord
{
    #[ORM\Id]
    #[ORM\Column(name: 'token_hash', length: 64)]
    private string $tokenHash;

    #[ORM\Column(name: 'user_id', length: 36)]
    private string $userId;

    #[ORM\Column(length: 32)]
    private string $purpose;

    #[ORM\Column(name: 'expires_at')]
    private DateTimeImmutable $expiresAt;

    #[ORM\Column(name: 'created_at')]
    private DateTimeImmutable $createdAt;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $subject = null;

    #[ORM\Column(name: 'consumed_at', nullable: true)]
    private ?DateTimeImmutable $consumedAt = null;

    public static function fromDomain(AccountToken $token): self
    {
        $record = new self();
        $record->apply($token);

        return $record;
    }

    public function updateFromDomain(AccountToken $token): void
    {
        $this->consumedAt = $token->consumedAt();
    }

    public function toDomain(): AccountToken
    {
        return AccountToken::reconstitute(
            TokenHash::fromHash($this->tokenHash),
            UserId::fromString($this->userId),
            AccountTokenPurpose::fromString($this->purpose),
            $this->expiresAt,
            $this->createdAt,
            $this->subject,
            $this->consumedAt,
        );
    }

    private function apply(AccountToken $token): void
    {
        $this->tokenHash = $token->hash()->value();
        $this->userId = $token->userId()->value();
        $this->purpose = $token->purpose()->value();
        $this->expiresAt = $token->expiresAt();
        $this->createdAt = $token->createdAt();
        $this->subject = $token->subject();
        $this->consumedAt = $token->consumedAt();
    }
}
