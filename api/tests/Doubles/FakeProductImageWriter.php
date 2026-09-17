<?php

declare(strict_types=1);

namespace App\Tests\Doubles;

use App\Product\Application\Image\GeneratedProductImage;
use App\Product\Application\Image\ProductImageWriter;

final class FakeProductImageWriter implements ProductImageWriter
{
    /** @var list<array{slug: string, label: string}> */
    public array $written = [];

    public function generate(string $slug, string $label): GeneratedProductImage
    {
        $this->written[] = ['slug' => $slug, 'label' => $label];

        $filename = str_ends_with($slug, '.svg') ? $slug : $slug.'.svg';

        return new GeneratedProductImage($filename, 'image/svg+xml', '<svg>'.$label.'</svg>');
    }
}
