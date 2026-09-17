<?php

declare(strict_types=1);

namespace App\Media\Application\Command;

final readonly class UploadMediaCommand
{
    public function __construct(
        public string $ownerType,
        public string $ownerId,
        public string $originalFilename,
        public string $mimeType,
        public string $binaryContent,
        public ?int $position = null,
    ) {
    }
}
