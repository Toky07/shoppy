<?php

declare(strict_types=1);

namespace App\Invoice\Domain\ValueObject;

use App\Invoice\Domain\Exception\InvalidInvoiceLineDescription;
use App\Invoice\Domain\Exception\InvalidInvoiceQuantity;
use App\Invoice\Domain\Exception\InvalidInvoiceUnitPrice;

final readonly class InvoiceLine
{
    private function __construct(
        private string $description,
        private int $quantity,
        private int $unitPriceCents,
    ) {
    }

    public static function of(string $description, int $quantity, int $unitPriceCents): self
    {
        $normalized = trim($description);

        if ($normalized === '') {
            throw new InvalidInvoiceLineDescription();
        }

        if ($quantity < 1) {
            throw new InvalidInvoiceQuantity();
        }

        if ($unitPriceCents < 0) {
            throw new InvalidInvoiceUnitPrice();
        }

        return new self($normalized, $quantity, $unitPriceCents);
    }

    public function description(): string
    {
        return $this->description;
    }

    public function quantity(): int
    {
        return $this->quantity;
    }

    public function unitPriceCents(): int
    {
        return $this->unitPriceCents;
    }

    public function lineTotalCents(): int
    {
        return $this->quantity * $this->unitPriceCents;
    }
}
