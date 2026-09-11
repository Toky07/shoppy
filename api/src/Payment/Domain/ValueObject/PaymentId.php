<?php

declare(strict_types=1);

namespace App\Payment\Domain\ValueObject;

use App\Payment\Domain\Exception\InvalidPaymentId;

final readonly class PaymentId
{
    private const UUID_PATTERN = '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i';

    private function __construct(private string $value)
    {
    }

    public static function fromString(string $value): self
    {
        if (preg_match(self::UUID_PATTERN, $value) !== 1) {
            throw new InvalidPaymentId();
        }

        return new self($value);
    }

    public static function generate(): self
    {
        $bytes = random_bytes(16);
        $bytes[6] = chr((ord($bytes[6]) & 0x0f) | 0x40);
        $bytes[8] = chr((ord($bytes[8]) & 0x3f) | 0x80);
        $hex = bin2hex($bytes);

        return self::fromString(sprintf(
            '%s-%s-%s-%s-%s',
            substr($hex, 0, 8),
            substr($hex, 8, 4),
            substr($hex, 12, 4),
            substr($hex, 16, 4),
            substr($hex, 20, 12),
        ));
    }

    public function value(): string
    {
        return $this->value;
    }
}
