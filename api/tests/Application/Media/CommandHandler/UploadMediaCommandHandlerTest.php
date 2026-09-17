<?php

declare(strict_types=1);

use App\Media\Application\Command\UploadMediaCommand;
use App\Media\Application\CommandHandler\UploadMediaCommandHandler;
use App\Media\Domain\Exception\InvalidMediaMimeType;
use App\Media\Domain\Exception\InvalidMediaSize;
use App\Media\Infrastructure\Persistence\InMemoryMediaRepository;
use App\Tests\Doubles\FakeMediaStorage;
use App\Tests\Doubles\FixedClock;

function tinyPng(): string
{
    return (string) base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==', true);
}

it('uploads a file and stores it under a wordpress-style path', function () {
    $repository = new InMemoryMediaRepository();
    $storage = new FakeMediaStorage();
    $handler = new UploadMediaCommandHandler(
        $repository,
        $storage,
        new FixedClock(new DateTimeImmutable('2026-09-16T12:00:00+00:00')),
    );

    $id = $handler->handle(new UploadMediaCommand(
        ownerType: 'product',
        ownerId: '550e8400-e29b-41d4-a716-446655440000',
        originalFilename: 'Nuvora Tee.PNG',
        mimeType: 'image/png',
        binaryContent: tinyPng(),
    ));

    $media = $repository->findById($id);

    expect($media)->not->toBeNull()
        ->and($media->ownerType()->className())->toBe('App\\Product\\Domain\\Entity\\Product')
        ->and($media->ownerId()->value())->toBe('550e8400-e29b-41d4-a716-446655440000')
        ->and($media->filename()->value())->toBe('nuvora-tee.png')
        ->and($media->relativePath()->value())->toBe('2026/09/nuvora-tee.png')
        ->and($media->publicUrl())->toBe('/uploads/2026/09/nuvora-tee.png')
        ->and($media->mimeType()->value())->toBe('image/png')
        ->and($media->position()->value())->toBe(0)
        ->and($storage->files['2026/09/nuvora-tee.png'])->toBe(tinyPng());
});

it('appends media after existing items when no position is given', function () {
    $repository = new InMemoryMediaRepository();
    $handler = new UploadMediaCommandHandler(
        $repository,
        new FakeMediaStorage(),
        new FixedClock(new DateTimeImmutable('2026-09-16T12:00:00+00:00')),
    );

    $handler->handle(new UploadMediaCommand(
        ownerType: 'product',
        ownerId: '550e8400-e29b-41d4-a716-446655440000',
        originalFilename: 'one.png',
        mimeType: 'image/png',
        binaryContent: tinyPng(),
    ));
    $second = $handler->handle(new UploadMediaCommand(
        ownerType: 'product',
        ownerId: '550e8400-e29b-41d4-a716-446655440000',
        originalFilename: 'two.png',
        mimeType: 'image/png',
        binaryContent: tinyPng(),
    ));

    expect($repository->findById($second)?->position()->value())->toBe(1);
});

it('uses an explicit gallery position', function () {
    $repository = new InMemoryMediaRepository();
    $handler = new UploadMediaCommandHandler(
        $repository,
        new FakeMediaStorage(),
        new FixedClock(new DateTimeImmutable('2026-09-16T12:00:00+00:00')),
    );

    $id = $handler->handle(new UploadMediaCommand(
        ownerType: 'product',
        ownerId: '550e8400-e29b-41d4-a716-446655440000',
        originalFilename: 'cover.png',
        mimeType: 'image/png',
        binaryContent: tinyPng(),
        position: 4,
    ));

    expect($repository->findById($id)?->position()->value())->toBe(4);
});

it('rejects an unsupported mime type', function () {
    $handler = new UploadMediaCommandHandler(
        new InMemoryMediaRepository(),
        new FakeMediaStorage(),
        new FixedClock(new DateTimeImmutable('2026-09-16T12:00:00+00:00')),
    );

    $handler->handle(new UploadMediaCommand(
        ownerType: 'product',
        ownerId: '550e8400-e29b-41d4-a716-446655440000',
        originalFilename: 'notes.pdf',
        mimeType: 'application/pdf',
        binaryContent: '%PDF',
    ));
})->throws(InvalidMediaMimeType::class);

it('rejects an empty file', function () {
    $handler = new UploadMediaCommandHandler(
        new InMemoryMediaRepository(),
        new FakeMediaStorage(),
        new FixedClock(new DateTimeImmutable('2026-09-16T12:00:00+00:00')),
    );

    $handler->handle(new UploadMediaCommand(
        ownerType: 'product',
        ownerId: '550e8400-e29b-41d4-a716-446655440000',
        originalFilename: 'empty.png',
        mimeType: 'image/png',
        binaryContent: '',
    ));
})->throws(InvalidMediaSize::class);
