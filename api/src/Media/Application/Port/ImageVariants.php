<?php

declare(strict_types=1);

namespace App\Media\Application\Port;

interface ImageVariants
{
    public function generate(string $relativePath, string $binaryContent, string $mimeType): void;

    public function delete(string $relativePath): void;
}
