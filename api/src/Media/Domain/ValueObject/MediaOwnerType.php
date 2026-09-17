<?php

declare(strict_types=1);

namespace App\Media\Domain\ValueObject;

use App\Media\Domain\Exception\InvalidMediaOwnerType;

final readonly class MediaOwnerType
{
    public const PRODUCT_ALIAS = 'product';

    public const PRODUCT_CLASS = 'App\\Product\\Domain\\Entity\\Product';

    /** @var array<string, string> */
    private const ALIASES = [
        self::PRODUCT_ALIAS => self::PRODUCT_CLASS,
    ];

    private function __construct(private string $className)
    {
    }

    public static function fromInput(string $value): self
    {
        $normalized = trim($value);

        if ($normalized === '') {
            throw new InvalidMediaOwnerType();
        }

        if (isset(self::ALIASES[$normalized])) {
            return new self(self::ALIASES[$normalized]);
        }

        $alias = array_search($normalized, self::ALIASES, true);
        if ($alias === false) {
            throw new InvalidMediaOwnerType();
        }

        return new self($normalized);
    }

    public function className(): string
    {
        return $this->className;
    }

    public function alias(): string
    {
        $alias = array_search($this->className, self::ALIASES, true);

        if ($alias === false) {
            throw new InvalidMediaOwnerType();
        }

        return $alias;
    }
}
