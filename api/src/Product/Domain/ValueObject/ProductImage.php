<?php

declare(strict_types=1);

namespace App\Product\Domain\ValueObject;

use App\Product\Domain\Exception\InvalidProductImage;

final readonly class ProductImage
{
    private const MAX_LENGTH = 2048;

    private function __construct(private string $value)
    {
    }

    public static function fromString(string $value): self
    {
        $normalized = trim($value);

        if ($normalized === '' || strlen($normalized) > self::MAX_LENGTH) {
            throw new InvalidProductImage();
        }

        if (self::isMediaPath($normalized) || self::isHttpUrl($normalized)) {
            return new self($normalized);
        }

        throw new InvalidProductImage();
    }

    public function value(): string
    {
        return $this->value;
    }

    private static function isMediaPath(string $value): bool
    {
        return (bool) preg_match('#^/media/products/[a-zA-Z0-9._-]+\\.(?:svg|png|jpe?g|webp)$#', $value);
    }

    private static function isHttpUrl(string $value): bool
    {
        if (filter_var($value, FILTER_VALIDATE_URL) === false) {
            return false;
        }

        $scheme = parse_url($value, PHP_URL_SCHEME);

        return $scheme === 'http' || $scheme === 'https';
    }
}
