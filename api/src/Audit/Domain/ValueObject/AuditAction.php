<?php

declare(strict_types=1);

namespace App\Audit\Domain\ValueObject;

use App\Audit\Domain\Exception\InvalidAuditAction;

final readonly class AuditAction
{
    public const ROLE_CHANGED = 'role_changed';

    public const PRICE_CHANGED = 'price_changed';

    public const STOCK_CHANGED = 'stock_changed';

    public const PAYMENT_CHANGED = 'payment_changed';

    private function __construct(private string $value)
    {
    }

    public static function roleChanged(): self
    {
        return new self(self::ROLE_CHANGED);
    }

    public static function priceChanged(): self
    {
        return new self(self::PRICE_CHANGED);
    }

    public static function stockChanged(): self
    {
        return new self(self::STOCK_CHANGED);
    }

    public static function paymentChanged(): self
    {
        return new self(self::PAYMENT_CHANGED);
    }

    public static function fromString(string $value): self
    {
        return match ($value) {
            self::ROLE_CHANGED => self::roleChanged(),
            self::PRICE_CHANGED => self::priceChanged(),
            self::STOCK_CHANGED => self::stockChanged(),
            self::PAYMENT_CHANGED => self::paymentChanged(),
            default => throw new InvalidAuditAction(),
        };
    }

    public function value(): string
    {
        return $this->value;
    }
}
