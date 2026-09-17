<?php

declare(strict_types=1);

use App\Product\Infrastructure\Csv\FileProductCsvReader;

it('ships a catalog of 100 products with at least 5 images each', function () {
    $rows = (new FileProductCsvReader())->read(dirname(__DIR__, 4).'/fixtures/products.csv');

    expect($rows)->toHaveCount(100);

    foreach ($rows as $row) {
        expect($row->name)->not->toBe('')
            ->and($row->priceCents)->toBeGreaterThan(0)
            ->and($row->images)->toHaveCount(5)
            ->and($row->images)->each->toStartWith('https://');
    }
});
