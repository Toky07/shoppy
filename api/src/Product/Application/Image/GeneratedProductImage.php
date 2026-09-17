<?php

declare(strict_types=1);

namespace App\Product\Application\Image;

final readonly class GeneratedProductImage
{
    public function __construct(
        public string $filename,
        public string $mimeType,
        public string $contents,
    ) {
    }
}
