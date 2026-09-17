<?php

declare(strict_types=1);

namespace App\Media\Domain\ValueObject;

use App\Media\Domain\Exception\InvalidMediaRelativePath;

final readonly class MediaRelativePath
{
    public const PUBLIC_PREFIX = '/uploads/';

    private const PATTERN = '/^\d{4}\/\d{2}\/[a-zA-Z0-9._-]+$/';

    private function __construct(private string $value)
    {
    }

    public static function fromString(string $value): self
    {
        if (preg_match(self::PATTERN, $value) !== 1 || str_contains($value, '..')) {
            throw new InvalidMediaRelativePath();
        }

        return new self($value);
    }

    public function value(): string
    {
        return $this->value;
    }

    public function publicUrl(): string
    {
        return self::PUBLIC_PREFIX.$this->value;
    }
}
