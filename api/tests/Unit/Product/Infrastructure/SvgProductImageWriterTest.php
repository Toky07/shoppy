<?php

declare(strict_types=1);

use App\Product\Infrastructure\Image\SvgProductImageWriter;

it('generates an svg placeholder without writing to disk', function () {
    $image = (new SvgProductImageWriter())->generate('001-tshirt-noir.svg', 'T-shirt Noir');

    expect($image->filename)->toBe('001-tshirt-noir.svg')
        ->and($image->mimeType)->toBe('image/svg+xml')
        ->and($image->contents)->toContain('T-shirt Noir')
        ->and($image->contents)->toContain('<svg');
});
