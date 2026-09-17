<?php

declare(strict_types=1);

namespace App\Media\Application\Port;

use DateTimeImmutable;

interface MediaStorage
{
    public function store(
        string $originalFilename,
        string $extension,
        string $binaryContent,
        DateTimeImmutable $now,
    ): StoredMediaFile;

    public function delete(string $relativePath): void;
}
