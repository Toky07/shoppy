<?php

declare(strict_types=1);

namespace App\Product\Application\Image;

interface ProductImageLoader
{
    public function load(string $source): GeneratedProductImage;
}
