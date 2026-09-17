<?php

declare(strict_types=1);

namespace App\Media\Domain\ValueObject;

use App\Media\Domain\Exception\InvalidMediaFilename;

final readonly class MediaFilename
{
    private const PATTERN = '/^[a-zA-Z0-9._-]+$/';

    private function __construct(private string $value)
    {
    }

    public static function fromString(string $value): self
    {
        $normalized = trim($value);

        if ($normalized === '' || preg_match(self::PATTERN, $normalized) !== 1) {
            throw new InvalidMediaFilename();
        }

        if (str_contains($normalized, '..')) {
            throw new InvalidMediaFilename();
        }

        return new self($normalized);
    }

    public function value(): string
    {
        return $this->value;
    }
}
