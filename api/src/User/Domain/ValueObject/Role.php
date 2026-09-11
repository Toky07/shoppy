<?php

declare(strict_types=1);

namespace App\User\Domain\ValueObject;

use App\User\Domain\Exception\InvalidUserRole;

final readonly class Role
{
    private const CUSTOMER = 'customer';
    private const ADMIN = 'admin';

    private function __construct(private string $value)
    {
    }

    public static function customer(): self
    {
        return new self(self::CUSTOMER);
    }

    public static function admin(): self
    {
        return new self(self::ADMIN);
    }

    public static function fromString(string $value): self
    {
        return match ($value) {
            self::CUSTOMER => self::customer(),
            self::ADMIN => self::admin(),
            default => throw new InvalidUserRole(),
        };
    }

    public function value(): string
    {
        return $this->value;
    }
}
