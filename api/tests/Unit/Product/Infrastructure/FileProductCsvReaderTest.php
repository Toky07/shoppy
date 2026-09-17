<?php

declare(strict_types=1);

use App\Product\Infrastructure\Csv\FileProductCsvReader;

it('reads products with several images', function () {
    $csv = tempnam(sys_get_temp_dir(), 'products').'.csv';
    file_put_contents($csv, <<<CSV
name,description,priceCents,stock,images
T-shirt Noir,"Coton bio, coupe droite.",1999,12,one.jpg|two.jpg|three.jpg|four.jpg|five.jpg
Mug Logo,,1299,8,https://cdn.shoppy.test/mug-1.png|https://cdn.shoppy.test/mug-2.png
CSV);

    $rows = (new FileProductCsvReader())->read($csv);
    unlink($csv);

    expect($rows)->toHaveCount(2)
        ->and($rows[0]->name)->toBe('T-shirt Noir')
        ->and($rows[0]->description)->toBe('Coton bio, coupe droite.')
        ->and($rows[0]->priceCents)->toBe(1999)
        ->and($rows[0]->stock)->toBe(12)
        ->and($rows[0]->images)->toBe(['one.jpg', 'two.jpg', 'three.jpg', 'four.jpg', 'five.jpg'])
        ->and($rows[1]->name)->toBe('Mug Logo')
        ->and($rows[1]->description)->toBeNull()
        ->and($rows[1]->images)->toBe([
            'https://cdn.shoppy.test/mug-1.png',
            'https://cdn.shoppy.test/mug-2.png',
        ]);
});

it('rejects a csv without an images column', function () {
    $csv = tempnam(sys_get_temp_dir(), 'products').'.csv';
    file_put_contents($csv, "name,description,priceCents,stock,image\nTee,,1999,1,tee.jpg\n");

    try {
        (new FileProductCsvReader())->read($csv);
    } finally {
        unlink($csv);
    }
})->throws(RuntimeException::class, 'Product CSV must start with name,description,priceCents,stock,images.');

it('rejects a row without images', function () {
    $csv = tempnam(sys_get_temp_dir(), 'products').'.csv';
    file_put_contents($csv, "name,description,priceCents,stock,images\nTee,,1999,1,\n");

    try {
        (new FileProductCsvReader())->read($csv);
    } finally {
        unlink($csv);
    }
})->throws(RuntimeException::class, 'Product CSV row must list at least one image.');
