<?php

declare(strict_types=1);

use App\Media\Domain\Entity\Media;
use App\Media\Domain\Repository\MediaRepository;
use App\Media\Domain\ValueObject\MediaFilename;
use App\Media\Domain\ValueObject\MediaId;
use App\Media\Domain\ValueObject\MediaMimeType;
use App\Media\Domain\ValueObject\MediaOwnerId;
use App\Media\Domain\ValueObject\MediaOwnerType;
use App\Media\Domain\ValueObject\MediaPosition;
use App\Media\Domain\ValueObject\MediaRelativePath;
use App\Media\Domain\ValueObject\MediaSize;
use Doctrine\ORM\EntityManagerInterface;

function persistMedia(
    MediaRepository $repository,
    string $id,
    string $filename,
    int $position,
    string $ownerId = '550e8400-e29b-41d4-a716-446655440000',
): void {
    $repository->save(Media::create(
        MediaId::fromString($id),
        MediaOwnerType::fromInput('product'),
        MediaOwnerId::fromString($ownerId),
        MediaFilename::fromString($filename),
        MediaRelativePath::fromString('2026/09/'.$filename),
        MediaMimeType::fromString('image/png'),
        MediaSize::fromInt(12),
        MediaPosition::fromInt($position),
        new DateTimeImmutable('2026-09-16T12:00:00+00:00'),
    ));
}

it('persists and retrieves media by id', function () {
    $id = MediaId::fromString('aa0e8400-e29b-41d4-a716-446655440000');
    $repository = self::getContainer()->get(MediaRepository::class);
    $entityManager = self::getContainer()->get(EntityManagerInterface::class);

    persistMedia($repository, $id->value(), 'tee.png', 0);
    $entityManager->clear();

    $found = $repository->findById($id);

    expect($found)->not->toBeNull()
        ->and($found->ownerType()->className())->toBe('App\\Product\\Domain\\Entity\\Product')
        ->and($found->publicUrl())->toBe('/uploads/2026/09/tee.png')
        ->and($found->filename()->value())->toBe('tee.png');
});

it('lists media for an owner ordered by position', function () {
    $repository = self::getContainer()->get(MediaRepository::class);
    $entityManager = self::getContainer()->get(EntityManagerInterface::class);
    $ownerId = '550e8400-e29b-41d4-a716-446655440000';

    persistMedia($repository, 'aa0e8400-e29b-41d4-a716-446655440001', 'second.png', 1, $ownerId);
    persistMedia($repository, 'aa0e8400-e29b-41d4-a716-446655440002', 'first.png', 0, $ownerId);
    persistMedia($repository, 'aa0e8400-e29b-41d4-a716-446655440003', 'other.png', 0, '660e8400-e29b-41d4-a716-446655440000');
    $entityManager->clear();

    $items = $repository->findByOwner(
        MediaOwnerType::fromInput('product'),
        MediaOwnerId::fromString($ownerId),
    );

    expect($items)->toHaveCount(2)
        ->and($items[0]->filename()->value())->toBe('first.png')
        ->and($items[1]->filename()->value())->toBe('second.png');
});

it('deletes persisted media', function () {
    $id = MediaId::fromString('aa0e8400-e29b-41d4-a716-446655440000');
    $repository = self::getContainer()->get(MediaRepository::class);
    $entityManager = self::getContainer()->get(EntityManagerInterface::class);

    persistMedia($repository, $id->value(), 'tee.png', 0);
    $media = $repository->findById($id);
    expect($media)->not->toBeNull();

    $repository->delete($media);
    $entityManager->clear();

    expect($repository->findById($id))->toBeNull();
});
