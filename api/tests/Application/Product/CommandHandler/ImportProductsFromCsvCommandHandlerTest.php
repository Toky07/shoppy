<?php

declare(strict_types=1);

use App\Media\Application\CommandHandler\UploadMediaCommandHandler;
use App\Media\Domain\ValueObject\MediaOwnerId;
use App\Media\Domain\ValueObject\MediaOwnerType;
use App\Media\Infrastructure\Persistence\InMemoryMediaRepository;
use App\Product\Application\Command\ImportProductsFromCsvCommand;
use App\Product\Application\CommandHandler\ImportProductsFromCsvCommandHandler;
use App\Product\Application\CommandHandler\SetProductStockCommandHandler;
use App\Product\Infrastructure\Csv\FileProductCsvReader;
use App\Product\Infrastructure\Persistence\InMemoryProductRepository;
use App\Tests\Doubles\FakeMediaStorage;
use App\Tests\Doubles\FakeProductImageLoader;
use App\Tests\Doubles\FixedClock;

it('imports products from csv and uploads every image as media', function () {
    $csv = tempnam(sys_get_temp_dir(), 'products').'.csv';
    file_put_contents($csv, <<<CSV
name,description,priceCents,stock,images
T-shirt Noir,Coton bio,1999,12,one.jpg|two.jpg|three.jpg|four.jpg|five.jpg
Mug Logo,,1299,8,https://cdn.shoppy.test/mug.png
CSV);
    $repository = new InMemoryProductRepository();
    $media = new InMemoryMediaRepository();
    $images = new FakeProductImageLoader();
    $handler = new ImportProductsFromCsvCommandHandler(
        new FileProductCsvReader(),
        $images,
        createProducts($repository, new FixedClock(new DateTimeImmutable('2026-08-20T12:00:00+00:00'))),
        $repository,
        new UploadMediaCommandHandler(
            $media,
            new FakeMediaStorage(),
            new FixedClock(new DateTimeImmutable('2026-09-16T12:00:00+00:00')),
        ),
        updateProducts($repository),
        new SetProductStockCommandHandler($repository),
    );

    $imported = $handler->handle(new ImportProductsFromCsvCommand($csv));
    unlink($csv);

    $tee = $repository->findByName(\App\Product\Domain\ValueObject\ProductName::fromString('T-shirt Noir'));
    $mug = $repository->findByName(\App\Product\Domain\ValueObject\ProductName::fromString('Mug Logo'));

    expect($imported)->toBe(2)
        ->and($repository->findPage(0, 20))->toHaveCount(2)
        ->and($images->loaded)->toBe([
            'one.jpg',
            'two.jpg',
            'three.jpg',
            'four.jpg',
            'five.jpg',
            'https://cdn.shoppy.test/mug.png',
        ])
        ->and($media->findByOwner(MediaOwnerType::fromInput('product'), MediaOwnerId::fromString($tee->id()->value())))
        ->toHaveCount(5)
        ->and($media->findByOwner(MediaOwnerType::fromInput('product'), MediaOwnerId::fromString($mug->id()->value())))
        ->toHaveCount(1);
});

it('updates products that already exist without duplicating images', function () {
    $csv = tempnam(sys_get_temp_dir(), 'products').'.csv';
    file_put_contents($csv, <<<CSV
name,description,priceCents,stock,images
T-shirt Noir,Coton bio,1999,12,one.jpg|two.jpg
CSV);
    $repository = new InMemoryProductRepository();
    $media = new InMemoryMediaRepository();
    $images = new FakeProductImageLoader();
    $handler = new ImportProductsFromCsvCommandHandler(
        new FileProductCsvReader(),
        $images,
        createProducts($repository, new FixedClock(new DateTimeImmutable('2026-08-20T12:00:00+00:00'))),
        $repository,
        new UploadMediaCommandHandler(
            $media,
            new FakeMediaStorage(),
            new FixedClock(new DateTimeImmutable('2026-09-16T12:00:00+00:00')),
        ),
        updateProducts($repository),
        new SetProductStockCommandHandler($repository),
    );

    $first = $handler->handle(new ImportProductsFromCsvCommand($csv));
    file_put_contents($csv, <<<CSV
name,description,priceCents,stock,images
T-shirt Noir,Coton peigné,2499,4,three.jpg
CSV);
    $second = $handler->handle(new ImportProductsFromCsvCommand($csv));
    unlink($csv);

    $tee = $repository->findByName(\App\Product\Domain\ValueObject\ProductName::fromString('T-shirt Noir'));

    expect($first)->toBe(1)
        ->and($second)->toBe(1)
        ->and($repository->countAll())->toBe(1)
        ->and($tee->price()->cents())->toBe(2499)
        ->and($tee->description()?->value())->toBe('Coton peigné')
        ->and($tee->stock()->value())->toBe(4)
        ->and($images->loaded)->toBe(['one.jpg', 'two.jpg'])
        ->and($media->findByOwner(MediaOwnerType::fromInput('product'), MediaOwnerId::fromString($tee->id()->value())))
        ->toHaveCount(2);
});
