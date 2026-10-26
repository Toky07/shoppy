<?php

declare(strict_types=1);

use App\Media\Application\Command\DeleteMediaCommand;
use App\Media\Application\Command\UploadMediaCommand;
use App\Media\Application\CommandHandler\DeleteMediaCommandHandler;
use App\Media\Domain\Exception\MediaNotFound;
use App\Media\Infrastructure\Persistence\InMemoryMediaRepository;
use App\Tests\Doubles\FakeImageVariants;
use App\Tests\Doubles\FakeMediaStorage;
use App\Tests\Doubles\FixedClock;

function tinyPngBytes(): string
{
    return (string) base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==', true);
}

it('deletes a media item and its stored file', function () {
    $repository = new InMemoryMediaRepository();
    $storage = new FakeMediaStorage();
    $variants = new FakeImageVariants();
    $upload = uploadMediaHandler(
        $repository,
        $storage,
        new FixedClock(new DateTimeImmutable('2026-09-16T12:00:00+00:00')),
    );
    $id = $upload->handle(new UploadMediaCommand(
        ownerType: 'product',
        ownerId: '550e8400-e29b-41d4-a716-446655440000',
        originalFilename: 'tee.png',
        mimeType: 'image/png',
        binaryContent: tinyPngBytes(),
    ));

    (new DeleteMediaCommandHandler($repository, $storage, $variants))->handle(new DeleteMediaCommand($id->value()));

    expect($repository->findById($id))->toBeNull()
        ->and($storage->files)->toBe([])
        ->and($variants->deleted)->toBe(['2026/09/tee.png']);
});

it('fails when deleting unknown media', function () {
    (new DeleteMediaCommandHandler(new InMemoryMediaRepository(), new FakeMediaStorage(), new FakeImageVariants()))->handle(
        new DeleteMediaCommand('aa0e8400-e29b-41d4-a716-446655440000'),
    );
})->throws(MediaNotFound::class);
