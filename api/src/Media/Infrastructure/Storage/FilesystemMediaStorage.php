<?php

declare(strict_types=1);

namespace App\Media\Infrastructure\Storage;

use App\Media\Application\Port\MediaStorage;
use App\Media\Application\Port\StoredMediaFile;
use DateTimeImmutable;
use RuntimeException;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

final readonly class FilesystemMediaStorage implements MediaStorage
{
    public function __construct(
        #[Autowire('%app.media.upload_dir%')]
        private string $uploadDirectory,
    ) {
    }

    public function store(
        string $originalFilename,
        string $extension,
        string $binaryContent,
        DateTimeImmutable $now,
    ): StoredMediaFile {
        $relativeDirectory = WordPressUploadPath::relativeDirectory($now);
        $directory = $this->uploadDirectory.'/'.$relativeDirectory;

        if (!is_dir($directory) && !mkdir($directory, 0777, true) && !is_dir($directory)) {
            throw new RuntimeException('Unable to create media upload directory.');
        }

        $filename = WordPressUploadPath::uniqueFilename(
            $directory,
            WordPressUploadPath::sanitizeFilename($originalFilename, $extension),
        );
        $absolutePath = $directory.'/'.$filename;

        if (file_put_contents($absolutePath, $binaryContent) === false) {
            throw new RuntimeException('Unable to write media file.');
        }

        return new StoredMediaFile($relativeDirectory.'/'.$filename, $filename, strlen($binaryContent));
    }

    public function delete(string $relativePath): void
    {
        $absolutePath = $this->uploadDirectory.'/'.$relativePath;

        if (is_file($absolutePath)) {
            unlink($absolutePath);
        }
    }
}
