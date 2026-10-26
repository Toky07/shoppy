<?php

declare(strict_types=1);

namespace App\Tests\Doubles;

use App\Media\Application\Port\ImageVariants;

final class FakeImageVariants implements ImageVariants
{
    /** @var list<array{path: string, mime: string, bytes: string}> */
    public array $generated = [];

    /** @var list<string> */
    public array $deleted = [];

    public function generate(string $relativePath, string $binaryContent, string $mimeType): void
    {
        $this->generated[] = [
            'path' => $relativePath,
            'mime' => $mimeType,
            'bytes' => $binaryContent,
        ];
    }

    public function delete(string $relativePath): void
    {
        $this->deleted[] = $relativePath;
    }
}
