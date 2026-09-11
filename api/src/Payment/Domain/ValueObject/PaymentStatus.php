<?php

declare(strict_types=1);

namespace App\Payment\Domain\ValueObject;

use App\Payment\Domain\Exception\InvalidPaymentStatus;

final readonly class PaymentStatus
{
    private const PENDING = 'pending';
    private const COMPLETED = 'completed';
    private const CANCELLED = 'cancelled';

    private function __construct(private string $value)
    {
    }

    public static function pending(): self
    {
        return new self(self::PENDING);
    }

    public static function completed(): self
    {
        return new self(self::COMPLETED);
    }

    public static function cancelled(): self
    {
        return new self(self::CANCELLED);
    }

    public static function fromString(string $value): self
    {
        return match ($value) {
            self::PENDING => self::pending(),
            self::COMPLETED => self::completed(),
            self::CANCELLED => self::cancelled(),
            default => throw new InvalidPaymentStatus(),
        };
    }

    public function value(): string
    {
        return $this->value;
    }

    public function isPending(): bool
    {
        return $this->value === self::PENDING;
    }

    public function isCompleted(): bool
    {
        return $this->value === self::COMPLETED;
    }

    public function isCancelled(): bool
    {
        return $this->value === self::CANCELLED;
    }
}
