<?php

declare(strict_types=1);

namespace App\Auth\Domain\ValueObject;

use App\Auth\Domain\Exception\InvalidAccountToken;

final readonly class AccountTokenPurpose
{
    public const PASSWORD_RESET = 'password_reset';

    public const EMAIL_VERIFICATION = 'email_verification';

    public const EMAIL_CHANGE = 'email_change';

    private function __construct(private string $value)
    {
    }

    public static function passwordReset(): self
    {
        return new self(self::PASSWORD_RESET);
    }

    public static function emailVerification(): self
    {
        return new self(self::EMAIL_VERIFICATION);
    }

    public static function emailChange(): self
    {
        return new self(self::EMAIL_CHANGE);
    }

    public static function fromString(string $value): self
    {
        return match ($value) {
            self::PASSWORD_RESET => self::passwordReset(),
            self::EMAIL_VERIFICATION => self::emailVerification(),
            self::EMAIL_CHANGE => self::emailChange(),
            default => throw new InvalidAccountToken(),
        };
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
