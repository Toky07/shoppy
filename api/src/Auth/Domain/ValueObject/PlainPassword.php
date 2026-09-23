<?php

declare(strict_types=1);

namespace App\Auth\Domain\ValueObject;

use App\Auth\Domain\Exception\InvalidPassword;

final readonly class PlainPassword
{
    private const MIN_LENGTH = 12;
    private const MAX_LENGTH = 4096;

    private function __construct(private string $value)
    {
    }

    public static function fromString(string $value): self
    {
        $length = strlen($value);

        if ($length < self::MIN_LENGTH || $length > self::MAX_LENGTH) {
            throw new InvalidPassword();
        }

        return new self($value);
    }

    public static function forVerification(string $value): self
    {
        if ($value === '' || strlen($value) > self::MAX_LENGTH) {
            throw new InvalidPassword();
        }

        return new self($value);
    }

    public function value(): string
    {
        return $this->value;
    }
}
