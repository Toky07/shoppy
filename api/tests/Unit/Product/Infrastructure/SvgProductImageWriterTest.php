<?php

declare(strict_types=1);

use App\Product\Infrastructure\Image\SvgProductImageWriter;

it('writes a unique svg placeholder and returns its public path', function () {
    $directory = sys_get_temp_dir().'/shoppy-images-'.bin2hex(random_bytes(4));
    $writer = new SvgProductImageWriter($directory);

    $path = $writer->write('001-tshirt-noir.svg', 'T-shirt Noir');
    $file = $directory.'/media/products/001-tshirt-noir.svg';

    expect($path)->toBe('/media/products/001-tshirt-noir.svg')
        ->and($file)->toBeFile()
        ->and((string) file_get_contents($file))->toContain('T-shirt Noir')
        ->and((string) file_get_contents($file))->toContain('<svg');
});
