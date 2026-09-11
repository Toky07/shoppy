<?php

declare(strict_types=1);

namespace App\Auth\Domain\ValueObject;

use App\Auth\Domain\Exception\InvalidHashedPassword;

final readonly class HashedPassword
{
    private function __construct(private string $value)
    {
    }

    public static function fromHash(string $value): self
    {
        if ($value === '') {
            throw new InvalidHashedPassword();
        }

        return new self($value);
    }

    public function value(): string
    {
        return $this->value;
    }
}
