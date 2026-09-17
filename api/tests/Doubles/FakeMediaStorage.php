<?php

declare(strict_types=1);

namespace App\Tests\Doubles;

use App\Media\Application\Port\MediaStorage;
use App\Media\Application\Port\StoredMediaFile;
use App\Media\Infrastructure\Storage\WordPressUploadPath;
use DateTimeImmutable;

final class FakeMediaStorage implements MediaStorage
{
    /** @var array<string, string> */
    public array $files = [];

    public function store(
        string $originalFilename,
        string $extension,
        string $binaryContent,
        DateTimeImmutable $now,
    ): StoredMediaFile {
        $directory = WordPressUploadPath::relativeDirectory($now);
        $filename = WordPressUploadPath::sanitizeFilename($originalFilename, $extension);
        $filename = $this->uniqueFilename($directory, $filename);
        $relativePath = $directory.'/'.$filename;
        $this->files[$relativePath] = $binaryContent;

        return new StoredMediaFile($relativePath, $filename, strlen($binaryContent));
    }

    public function delete(string $relativePath): void
    {
        unset($this->files[$relativePath]);
    }

    private function uniqueFilename(string $directory, string $filename): string
    {
        $candidate = $filename;
        $number = 1;
        $name = pathinfo($filename, PATHINFO_FILENAME);
        $extension = pathinfo($filename, PATHINFO_EXTENSION);

        while (isset($this->files[$directory.'/'.$candidate])) {
            $candidate = $name.'-'.$number.'.'.$extension;
            ++$number;
        }

        return $candidate;
    }
}
