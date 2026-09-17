<?php

declare(strict_types=1);

use App\Media\Application\Command\DeleteMediaByOwnerCommand;
use App\Media\Application\Command\UploadMediaCommand;
use App\Media\Application\CommandHandler\DeleteMediaByOwnerCommandHandler;
use App\Media\Application\CommandHandler\UploadMediaCommandHandler;
use App\Media\Domain\ValueObject\MediaOwnerId;
use App\Media\Domain\ValueObject\MediaOwnerType;
use App\Media\Infrastructure\Persistence\InMemoryMediaRepository;
use App\Tests\Doubles\FakeMediaStorage;
use App\Tests\Doubles\FixedClock;

function pngBytes(): string
{
    return (string) base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==', true);
}

it('deletes every media item attached to an owner', function () {
    $repository = new InMemoryMediaRepository();
    $storage = new FakeMediaStorage();
    $upload = new UploadMediaCommandHandler(
        $repository,
        $storage,
        new FixedClock(new DateTimeImmutable('2026-09-16T12:00:00+00:00')),
    );
    $ownerId = '550e8400-e29b-41d4-a716-446655440000';

    $upload->handle(new UploadMediaCommand(
        ownerType: 'product',
        ownerId: $ownerId,
        originalFilename: 'one.png',
        mimeType: 'image/png',
        binaryContent: pngBytes(),
    ));
    $upload->handle(new UploadMediaCommand(
        ownerType: 'product',
        ownerId: $ownerId,
        originalFilename: 'two.png',
        mimeType: 'image/png',
        binaryContent: pngBytes(),
    ));
    $kept = $upload->handle(new UploadMediaCommand(
        ownerType: 'product',
        ownerId: '660e8400-e29b-41d4-a716-446655440000',
        originalFilename: 'other.png',
        mimeType: 'image/png',
        binaryContent: pngBytes(),
    ));

    (new DeleteMediaByOwnerCommandHandler($repository, $storage))->handle(
        new DeleteMediaByOwnerCommand(ownerType: 'product', ownerId: $ownerId),
    );

    expect($repository->findByOwner(
        MediaOwnerType::fromInput('product'),
        MediaOwnerId::fromString($ownerId),
    ))->toBe([])
        ->and($repository->findById($kept))->not->toBeNull()
        ->and($storage->files)->toHaveCount(1);
});
