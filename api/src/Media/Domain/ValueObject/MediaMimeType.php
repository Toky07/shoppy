<?php

declare(strict_types=1);

namespace App\Media\Domain\ValueObject;

use App\Media\Domain\Exception\InvalidMediaMimeType;

final readonly class MediaMimeType
{
    /** @var array<string, string> */
    private const EXTENSIONS = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/gif' => 'gif',
        'image/svg+xml' => 'svg',
    ];

    private function __construct(private string $value)
    {
    }

    public static function fromString(string $value): self
    {
        $normalized = strtolower(trim($value));

        if (!isset(self::EXTENSIONS[$normalized])) {
            throw new InvalidMediaMimeType();
        }

        return new self($normalized);
    }

    public function value(): string
    {
        return $this->value;
    }

    public function extension(): string
    {
        return self::EXTENSIONS[$this->value];
    }
}
