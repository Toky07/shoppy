<?php

declare(strict_types=1);

namespace App\Payment\Domain\ValueObject;

use App\Payment\Domain\Exception\InvalidOrderReference;

final readonly class OrderReference
{
    private const UUID_PATTERN = '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i';

    private function __construct(private string $value)
    {
    }

    public static function fromString(string $value): self
    {
        if (preg_match(self::UUID_PATTERN, $value) !== 1) {
            throw new InvalidOrderReference();
        }

        return new self($value);
    }

    public function value(): string
    {
        return $this->value;
    }
}
