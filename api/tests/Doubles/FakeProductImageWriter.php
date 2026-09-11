<?php

declare(strict_types=1);

namespace App\Tests\Doubles;

use App\Product\Application\Image\ProductImageWriter;

final class FakeProductImageWriter implements ProductImageWriter
{
    /** @var list<array{slug: string, label: string}> */
    public array $written = [];

    public function write(string $slug, string $label): string
    {
        $this->written[] = ['slug' => $slug, 'label' => $label];

        $filename = str_ends_with($slug, '.svg') ? $slug : $slug.'.svg';

        return '/media/products/'.$filename;
    }
}
