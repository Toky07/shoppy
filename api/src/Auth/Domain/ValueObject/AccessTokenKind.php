<?php

declare(strict_types=1);

namespace App\Auth\Domain\ValueObject;

use App\Auth\Domain\Exception\InvalidAccessTokenKind;

final readonly class AccessTokenKind
{
    public const ACCESS = 'access';

    public const REFRESH = 'refresh';

    private function __construct(private string $value)
    {
    }

    public static function access(): self
    {
        return new self(self::ACCESS);
    }

    public static function refresh(): self
    {
        return new self(self::REFRESH);
    }

    public static function fromString(string $value): self
    {
        return match ($value) {
            self::ACCESS => self::access(),
            self::REFRESH => self::refresh(),
            default => throw new InvalidAccessTokenKind(),
        };
    }

    public function value(): string
    {
        return $this->value;
    }

    public function isAccess(): bool
    {
        return $this->value === self::ACCESS;
    }

    public function isRefresh(): bool
    {
        return $this->value === self::REFRESH;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
