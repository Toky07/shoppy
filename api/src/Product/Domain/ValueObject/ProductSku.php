<?php

declare(strict_types=1);

namespace App\Product\Domain\ValueObject;

use App\Product\Domain\Exception\InvalidProductSku;

final readonly class ProductSku
{
    public const MAX_LENGTH = 40;

    private const PATTERN = '/^[A-Z0-9]+(?:-[A-Z0-9]+)*$/';

    private function __construct(private string $value)
    {
    }

    public static function fromString(string $value): self
    {
        $normalized = strtoupper(trim($value));

        if (preg_match(self::PATTERN, $normalized) !== 1 || strlen($normalized) > self::MAX_LENGTH) {
            throw new InvalidProductSku();
        }

        return new self($normalized);
    }

    public static function fromName(ProductName $name): self
    {
        $ascii = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $name->value());
        $sku = strtoupper($ascii === false ? $name->value() : $ascii);
        $sku = preg_replace('/[^A-Z0-9]+/', '-', $sku) ?? $sku;
        $sku = trim($sku, '-');

        if ($sku === '') {
            $sku = 'PRODUIT';
        }

        if (strlen($sku) > self::MAX_LENGTH) {
            $sku = rtrim(substr($sku, 0, self::MAX_LENGTH), '-');
        }

        return self::fromString($sku);
    }

    public function withCopyNumber(int $number): self
    {
        if ($number < 2) {
            return $this;
        }

        $suffix = '-'.$number;
        $maxBase = self::MAX_LENGTH - strlen($suffix);
        $base = rtrim(substr($this->value, 0, $maxBase), '-');

        return self::fromString($base.$suffix);
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
