<?php

declare(strict_types=1);

namespace App\Media\Infrastructure\Storage;

use DateTimeImmutable;

final class WordPressUploadPath
{
    public static function relativeDirectory(DateTimeImmutable $now): string
    {
        return $now->format('Y/m');
    }

    public static function sanitizeFilename(string $originalFilename, string $extension): string
    {
        $basename = pathinfo($originalFilename, PATHINFO_FILENAME);
        $slug = strtolower($basename);
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug) ?? $slug;
        $slug = trim($slug, '-');

        if ($slug === '') {
            $slug = 'file';
        }

        return $slug.'.'.$extension;
    }

    public static function uniqueFilename(string $directory, string $filename): string
    {
        $candidate = $filename;
        $number = 1;
        $name = pathinfo($filename, PATHINFO_FILENAME);
        $extension = pathinfo($filename, PATHINFO_EXTENSION);

        while (is_file($directory.'/'.$candidate)) {
            $candidate = $name.'-'.$number.'.'.$extension;
            ++$number;
        }

        return $candidate;
    }
}
