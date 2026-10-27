<?php

declare(strict_types=1);

namespace App\Invoice\Domain\ValueObject;

use App\Invoice\Domain\Exception\InvalidInvoiceLogoSize;

final readonly class InvoiceLogoSize
{
    public const MAX_BYTES = 2 * 1024 * 1024;

    private function __construct(private int $value)
    {
    }

    public static function fromInt(int $value): self
    {
        if ($value < 1 || $value > self::MAX_BYTES) {
            throw new InvalidInvoiceLogoSize();
        }

        return new self($value);
    }

    public function value(): int
    {
        return $this->value;
    }
}
