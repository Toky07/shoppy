<?php

declare(strict_types=1);

use App\Media\Application\Command\UploadMediaCommand;
use App\Media\Application\CommandHandler\UploadMediaCommandHandler;
use App\Media\Application\Query\ListMediaByOwnerQuery;
use App\Media\Application\QueryHandler\ListMediaByOwnerQueryHandler;
use App\Media\Infrastructure\Persistence\InMemoryMediaRepository;
use App\Tests\Doubles\FakeMediaStorage;
use App\Tests\Doubles\FixedClock;

it('lists media for an owner ordered by position', function () {
    $repository = new InMemoryMediaRepository();
    $upload = new UploadMediaCommandHandler(
        $repository,
        new FakeMediaStorage(),
        new FixedClock(new DateTimeImmutable('2026-09-16T12:00:00+00:00')),
    );
    $png = (string) base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==', true);
    $ownerId = '550e8400-e29b-41d4-a716-446655440000';

    $upload->handle(new UploadMediaCommand(
        ownerType: 'product',
        ownerId: $ownerId,
        originalFilename: 'second.png',
        mimeType: 'image/png',
        binaryContent: $png,
        position: 1,
    ));
    $first = $upload->handle(new UploadMediaCommand(
        ownerType: 'product',
        ownerId: $ownerId,
        originalFilename: 'first.png',
        mimeType: 'image/png',
        binaryContent: $png,
        position: 0,
    ));

    $response = (new ListMediaByOwnerQueryHandler($repository))->handle(
        new ListMediaByOwnerQuery(ownerType: 'product', ownerId: $ownerId),
    );

    expect($response->toArray()['items'])->toHaveCount(2)
        ->and($response->toArray()['items'][0]['id'])->toBe($first->value())
        ->and($response->toArray()['items'][0]['ownerType'])->toBe('product')
        ->and($response->toArray()['items'][0]['ownerId'])->toBe($ownerId)
        ->and($response->toArray()['items'][0]['url'])->toBe('/uploads/2026/09/first.png')
        ->and($response->toArray()['items'][0]['filename'])->toBe('first.png')
        ->and($response->toArray()['items'][0]['mimeType'])->toBe('image/png')
        ->and($response->toArray()['items'][0]['position'])->toBe(0)
        ->and($response->toArray()['items'][1]['filename'])->toBe('second.png');
});
