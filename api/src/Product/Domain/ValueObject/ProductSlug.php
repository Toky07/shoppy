<?php

declare(strict_types=1);

namespace App\Product\Domain\ValueObject;

use App\Product\Domain\Exception\InvalidProductSlug;

final readonly class ProductSlug
{
    public const MAX_LENGTH = 180;

    private const PATTERN = '/^[a-z0-9]+(?:-[a-z0-9]+)*$/';

    private function __construct(private string $value)
    {
    }

    public static function fromString(string $value): self
    {
        if (preg_match(self::PATTERN, $value) !== 1 || strlen($value) > self::MAX_LENGTH) {
            throw new InvalidProductSlug();
        }

        return new self($value);
    }

    public static function fromName(ProductName $name): self
    {
        return self::fromString(self::slugify($name->value()));
    }

    public function withCopyNumber(int $number): self
    {
        if ($number < 2) {
            return $this;
        }

        $suffix = '-'.$number;
        $maxBase = self::MAX_LENGTH - strlen($suffix);
        $base = $this->value;

        if (strlen($base) > $maxBase) {
            $base = rtrim(substr($base, 0, $maxBase), '-');
        }

        return self::fromString($base.$suffix);
    }

    public function value(): string
    {
        return $this->value;
    }

    private static function slugify(string $value): string
    {
        $ascii = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
        $slug = strtolower($ascii === false ? $value : $ascii);
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug) ?? $slug;
        $slug = trim($slug, '-');

        if ($slug === '') {
            $slug = 'produit';
        }

        if (strlen($slug) > self::MAX_LENGTH) {
            $slug = rtrim(substr($slug, 0, self::MAX_LENGTH), '-');
        }

        return $slug;
    }
}
