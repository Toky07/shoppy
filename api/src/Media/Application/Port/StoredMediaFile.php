<?php

declare(strict_types=1);

namespace App\Media\Application\Port;

final readonly class StoredMediaFile
{
    public function __construct(
        public string $relativePath,
        public string $filename,
        public int $size,
    ) {
    }
}
