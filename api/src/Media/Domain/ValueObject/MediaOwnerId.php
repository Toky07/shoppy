<?php

declare(strict_types=1);

namespace App\Media\Domain\ValueObject;

use App\Media\Domain\Exception\InvalidMediaOwnerId;

final readonly class MediaOwnerId
{
    private const UUID_PATTERN = '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i';

    private function __construct(private string $value)
    {
    }

    public static function fromString(string $value): self
    {
        if (preg_match(self::UUID_PATTERN, $value) !== 1) {
            throw new InvalidMediaOwnerId();
        }

        return new self($value);
    }

    public function value(): string
    {
        return $this->value;
    }
}
