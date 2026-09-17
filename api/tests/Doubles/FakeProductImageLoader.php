<?php

declare(strict_types=1);

namespace App\Tests\Doubles;

use App\Product\Application\Image\GeneratedProductImage;
use App\Product\Application\Image\ProductImageLoader;

final class FakeProductImageLoader implements ProductImageLoader
{
    /** @var list<string> */
    public array $loaded = [];

    public function load(string $source): GeneratedProductImage
    {
        $this->loaded[] = $source;

        $filename = basename(parse_url($source, PHP_URL_PATH) ?: $source);
        if ($filename === '' || $filename === '/') {
            $filename = 'image.png';
        }

        if (!str_contains($filename, '.')) {
            $filename .= '.png';
        }

        return new GeneratedProductImage(
            $filename,
            'image/png',
            (string) base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==', true),
        );
    }
}
