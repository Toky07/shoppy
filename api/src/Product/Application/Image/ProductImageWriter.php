<?php

declare(strict_types=1);

namespace App\Product\Application\Image;

interface ProductImageWriter
{
    public function generate(string $slug, string $label): GeneratedProductImage;
}
