<?php

declare(strict_types=1);

use App\Media\Application\Command\UploadMediaCommand;
use App\Media\Application\CommandHandler\DeleteMediaByOwnerCommandHandler;
use App\Media\Application\CommandHandler\UploadMediaCommandHandler;
use App\Media\Infrastructure\Persistence\InMemoryMediaRepository;
use App\Product\Application\Command\CreateProductCommand;
use App\Product\Application\Command\DeleteProductCommand;
use App\Product\Application\CommandHandler\DeleteProductCommandHandler;
use App\Product\Domain\Exception\ProductNotFound;
use App\Product\Infrastructure\Persistence\InMemoryProductRepository;
use App\Tests\Doubles\FakeMediaStorage;
use App\Tests\Doubles\FixedClock;

it('deletes a product', function () {
    $repository = new InMemoryProductRepository();
    $id = createProducts(
        $repository,
        new FixedClock(new DateTimeImmutable('2026-08-20T12:00:00+00:00')),
    )->handle(new CreateProductCommand(name: 'Nuvora Tee', priceCents: 1999));

    (new DeleteProductCommandHandler(
        $repository,
        new DeleteMediaByOwnerCommandHandler(new InMemoryMediaRepository(), new FakeMediaStorage()),
    ))->handle(new DeleteProductCommand($id->value()));

    expect($repository->findById($id))->toBeNull();
});

it('deletes attached media when deleting a product', function () {
    $products = new InMemoryProductRepository();
    $media = new InMemoryMediaRepository();
    $storage = new FakeMediaStorage();
    $clock = new FixedClock(new DateTimeImmutable('2026-09-16T12:00:00+00:00'));
    $id = createProducts($products, $clock)->handle(
        new CreateProductCommand(name: 'Nuvora Tee', priceCents: 1999),
    );
    (new UploadMediaCommandHandler($media, $storage, $clock))->handle(new UploadMediaCommand(
        ownerType: 'product',
        ownerId: $id->value(),
        originalFilename: 'tee.png',
        mimeType: 'image/png',
        binaryContent: (string) base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==', true),
    ));

    (new DeleteProductCommandHandler(
        $products,
        new DeleteMediaByOwnerCommandHandler($media, $storage),
    ))->handle(new DeleteProductCommand($id->value()));

    expect($media->findByOwner(
        \App\Media\Domain\ValueObject\MediaOwnerType::fromInput('product'),
        \App\Media\Domain\ValueObject\MediaOwnerId::fromString($id->value()),
    ))->toBe([])
        ->and($storage->files)->toBe([]);
});

it('fails when deleting an unknown product', function () {
    (new DeleteProductCommandHandler(
        new InMemoryProductRepository(),
        new DeleteMediaByOwnerCommandHandler(new InMemoryMediaRepository(), new FakeMediaStorage()),
    ))->handle(new DeleteProductCommand('550e8400-e29b-41d4-a716-446655440000'));
})->throws(ProductNotFound::class);
