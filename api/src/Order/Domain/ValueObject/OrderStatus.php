<?php

declare(strict_types=1);

namespace App\Order\Domain\ValueObject;

use App\Order\Domain\Exception\InvalidOrderStatus;

final readonly class OrderStatus
{
    private const PENDING = 'pending';
    private const CANCELLED = 'cancelled';
    private const PAID = 'paid';

    private function __construct(private string $value)
    {
    }

    public static function pending(): self
    {
        return new self(self::PENDING);
    }

    public static function cancelled(): self
    {
        return new self(self::CANCELLED);
    }

    public static function paid(): self
    {
        return new self(self::PAID);
    }

    public static function fromString(string $value): self
    {
        return match ($value) {
            self::PENDING => self::pending(),
            self::CANCELLED => self::cancelled(),
            self::PAID => self::paid(),
            default => throw new InvalidOrderStatus(),
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

    public function isPending(): bool
    {
        return $this->value === self::PENDING;
    }
}
