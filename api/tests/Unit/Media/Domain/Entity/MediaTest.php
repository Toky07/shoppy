<?php

declare(strict_types=1);

use App\Media\Domain\Entity\Media;
use App\Media\Domain\ValueObject\MediaFilename;
use App\Media\Domain\ValueObject\MediaId;
use App\Media\Domain\ValueObject\MediaMimeType;
use App\Media\Domain\ValueObject\MediaOwnerId;
use App\Media\Domain\ValueObject\MediaOwnerType;
use App\Media\Domain\ValueObject\MediaPosition;
use App\Media\Domain\ValueObject\MediaRelativePath;
use App\Media\Domain\ValueObject\MediaSize;

it('exposes its identity, owner and stored file', function () {
    $id = MediaId::fromString('aa0e8400-e29b-41d4-a716-446655440000');
    $createdAt = new DateTimeImmutable('2026-09-16T12:00:00+00:00');

    $media = Media::create(
        $id,
        MediaOwnerType::fromInput('product'),
        MediaOwnerId::fromString('550e8400-e29b-41d4-a716-446655440000'),
        MediaFilename::fromString('nuvora-tee.jpg'),
        MediaRelativePath::fromString('2026/09/nuvora-tee.jpg'),
        MediaMimeType::fromString('image/jpeg'),
        MediaSize::fromInt(2048),
        MediaPosition::fromInt(0),
        $createdAt,
    );

    expect($media->id())->toBe($id)
        ->and($media->ownerType()->alias())->toBe('product')
        ->and($media->ownerType()->className())->toBe('App\\Product\\Domain\\Entity\\Product')
        ->and($media->ownerId()->value())->toBe('550e8400-e29b-41d4-a716-446655440000')
        ->and($media->filename()->value())->toBe('nuvora-tee.jpg')
        ->and($media->relativePath()->value())->toBe('2026/09/nuvora-tee.jpg')
        ->and($media->publicUrl())->toBe('/uploads/2026/09/nuvora-tee.jpg')
        ->and($media->mimeType()->value())->toBe('image/jpeg')
        ->and($media->size()->value())->toBe(2048)
        ->and($media->position()->value())->toBe(0)
        ->and($media->createdAt())->toBe($createdAt);
});
