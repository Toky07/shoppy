<?php

declare(strict_types=1);

namespace App\Media\Application;

use App\Media\Domain\Exception\InvalidMediaMimeType;
use App\Media\Domain\ValueObject\MediaMimeType;
use finfo;

final class DetectedMediaType
{
    /** @var list<string> */
    private const ALLOWED = [
        'image/jpeg',
        'image/png',
        'image/webp',
        'image/gif',
    ];

    public static function fromBinary(string $contents): MediaMimeType
    {
        if ($contents === '' || self::looksLikeSvg($contents)) {
            throw new InvalidMediaMimeType();
        }

        $detected = strtolower((string) (new finfo(FILEINFO_MIME_TYPE))->buffer($contents));

        if (!in_array($detected, self::ALLOWED, true)) {
            throw new InvalidMediaMimeType();
        }

        return MediaMimeType::fromString($detected);
    }

    private static function looksLikeSvg(string $contents): bool
    {
        $sample = strtolower(substr($contents, 0, 512));

        return str_contains($sample, '<svg') || str_contains($sample, 'image/svg');
    }
}
