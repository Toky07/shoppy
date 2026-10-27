<?php

declare(strict_types=1);

namespace App\Invoice\Domain\ValueObject;

use App\Invoice\Domain\Exception\InvalidInvoiceNumber;

final readonly class InvoiceNumber
{
    private const PATTERN = '/^FAC-(\d{4})-(\d{4,})$/';

    private function __construct(
        private string $value,
        private int $year,
        private int $sequence,
    ) {
    }

    public static function of(int $year, int $sequence): self
    {
        if ($year < 2000 || $year > 2100 || $sequence < 1) {
            throw new InvalidInvoiceNumber();
        }

        return new self(sprintf('FAC-%d-%04d', $year, $sequence), $year, $sequence);
    }

    public static function fromString(string $value): self
    {
        if (preg_match(self::PATTERN, $value, $matches) !== 1) {
            throw new InvalidInvoiceNumber();
        }

        $year = (int) $matches[1];
        $sequence = (int) $matches[2];

        if ($year < 2000 || $year > 2100 || $sequence < 1) {
            throw new InvalidInvoiceNumber();
        }

        return new self(sprintf('FAC-%d-%04d', $year, $sequence), $year, $sequence);
    }

    public function value(): string
    {
        return $this->value;
    }

    public function year(): int
    {
        return $this->year;
    }

    public function sequence(): int
    {
        return $this->sequence;
    }
}
