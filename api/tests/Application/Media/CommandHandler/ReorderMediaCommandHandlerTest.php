<?php

declare(strict_types=1);

use App\Media\Application\Command\ReorderMediaCommand;
use App\Media\Application\Command\UploadMediaCommand;
use App\Media\Application\CommandHandler\ReorderMediaCommandHandler;
use App\Media\Application\CommandHandler\UploadMediaCommandHandler;
use App\Media\Domain\Exception\InvalidMediaOrder;
use App\Media\Domain\ValueObject\MediaOwnerId;
use App\Media\Domain\ValueObject\MediaOwnerType;
use App\Media\Infrastructure\Persistence\InMemoryMediaRepository;
use App\Tests\Doubles\FakeMediaStorage;
use App\Tests\Doubles\FixedClock;

it('reorders every image of a product', function () {
    $media = new InMemoryMediaRepository();
    $upload = new UploadMediaCommandHandler(
        $media,
        new FakeMediaStorage(),
        new FixedClock(new DateTimeImmutable('2026-09-22T12:00:00+00:00')),
    );
    $ownerId = '550e8400-e29b-41d4-a716-446655440001';
    $first = $upload->handle(new UploadMediaCommand('product', $ownerId, 'one.jpg', 'image/jpeg', 'one'));
    $second = $upload->handle(new UploadMediaCommand('product', $ownerId, 'two.jpg', 'image/jpeg', 'two'));

    (new ReorderMediaCommandHandler($media))->handle(new ReorderMediaCommand(
        'product',
        $ownerId,
        [$second->value(), $first->value()],
    ));

    $ordered = $media->findByOwner(MediaOwnerType::fromInput('product'), MediaOwnerId::fromString($ownerId));

    expect($ordered[0]->id()->value())->toBe($second->value())
        ->and($ordered[0]->position()->value())->toBe(0)
        ->and($ordered[1]->id()->value())->toBe($first->value())
        ->and($ordered[1]->position()->value())->toBe(1);
});

it('rejects an order that drops an image', function () {
    $media = new InMemoryMediaRepository();
    $upload = new UploadMediaCommandHandler(
        $media,
        new FakeMediaStorage(),
        new FixedClock(new DateTimeImmutable('2026-09-22T12:00:00+00:00')),
    );
    $ownerId = '550e8400-e29b-41d4-a716-446655440001';
    $first = $upload->handle(new UploadMediaCommand('product', $ownerId, 'one.jpg', 'image/jpeg', 'one'));
    $upload->handle(new UploadMediaCommand('product', $ownerId, 'two.jpg', 'image/jpeg', 'two'));

    expect(fn () => (new ReorderMediaCommandHandler($media))->handle(new ReorderMediaCommand(
        'product',
        $ownerId,
        [$first->value()],
    )))->toThrow(InvalidMediaOrder::class);
});
