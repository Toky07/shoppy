<?php

declare(strict_types=1);

use App\Product\Application\Command\ImportProductsFromCsvCommand;
use App\Product\Application\CommandHandler\CreateProductCommandHandler;
use App\Product\Application\CommandHandler\ImportProductsFromCsvCommandHandler;
use App\Product\Infrastructure\Csv\FileProductCsvReader;
use App\Product\Infrastructure\Persistence\InMemoryProductRepository;
use App\Tests\Doubles\FakeProductImageWriter;
use App\Tests\Doubles\FixedClock;

it('imports products from csv and writes images', function () {
    $csv = tempnam(sys_get_temp_dir(), 'products').'.csv';
    file_put_contents($csv, <<<CSV
name,description,priceCents,stock,image
T-shirt Noir,Coton bio,1999,12,001-tshirt-noir.svg
Mug Logo,,1299,8,https://cdn.shoppy.test/mug.png
CSV);
    $repository = new InMemoryProductRepository();
    $images = new FakeProductImageWriter();
    $handler = new ImportProductsFromCsvCommandHandler(
        new FileProductCsvReader(),
        $images,
        new CreateProductCommandHandler($repository, new FixedClock(new DateTimeImmutable('2026-08-20T12:00:00+00:00'))),
        $repository,
    );

    $imported = $handler->handle(new ImportProductsFromCsvCommand($csv));
    unlink($csv);

    $products = $repository->findPage(0, 20);

    expect($imported)->toBe(2)
        ->and($products)->toHaveCount(2)
        ->and($images->written)->toEqual([['slug' => '001-tshirt-noir.svg', 'label' => 'T-shirt Noir']])
        ->and($repository->findByName(\App\Product\Domain\ValueObject\ProductName::fromString('T-shirt Noir'))?->image()?->value())
        ->toBe('/media/products/001-tshirt-noir.svg')
        ->and($repository->findByName(\App\Product\Domain\ValueObject\ProductName::fromString('Mug Logo'))?->image()?->value())
        ->toBe('https://cdn.shoppy.test/mug.png');
});

it('skips products that already exist', function () {
    $csv = tempnam(sys_get_temp_dir(), 'products').'.csv';
    file_put_contents($csv, <<<CSV
name,description,priceCents,stock,image
T-shirt Noir,Coton bio,1999,12,001-tshirt-noir.svg
CSV);
    $repository = new InMemoryProductRepository();
    $create = new CreateProductCommandHandler($repository, new FixedClock(new DateTimeImmutable('2026-08-20T12:00:00+00:00')));
    $handler = new ImportProductsFromCsvCommandHandler(
        new FileProductCsvReader(),
        new FakeProductImageWriter(),
        $create,
        $repository,
    );

    $first = $handler->handle(new ImportProductsFromCsvCommand($csv));
    $second = $handler->handle(new ImportProductsFromCsvCommand($csv));
    unlink($csv);

    expect($first)->toBe(1)
        ->and($second)->toBe(0)
        ->and($repository->countAll())->toBe(1);
});
