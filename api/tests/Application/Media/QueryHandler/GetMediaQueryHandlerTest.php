<?php

declare(strict_types=1);

use App\Media\Application\Command\UploadMediaCommand;
use App\Media\Application\CommandHandler\UploadMediaCommandHandler;
use App\Media\Application\Query\GetMediaQuery;
use App\Media\Application\QueryHandler\GetMediaQueryHandler;
use App\Media\Domain\Exception\MediaNotFound;
use App\Media\Infrastructure\Persistence\InMemoryMediaRepository;
use App\Tests\Doubles\FakeMediaStorage;
use App\Tests\Doubles\FixedClock;

it('returns uploaded media by id', function () {
    $repository = new InMemoryMediaRepository();
    $id = (new UploadMediaCommandHandler(
        $repository,
        new FakeMediaStorage(),
        new FixedClock(new DateTimeImmutable('2026-09-16T12:00:00+00:00')),
    ))->handle(new UploadMediaCommand(
        ownerType: 'product',
        ownerId: '550e8400-e29b-41d4-a716-446655440000',
        originalFilename: 'tee.png',
        mimeType: 'image/png',
        binaryContent: (string) base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==', true),
    ));

    $response = (new GetMediaQueryHandler($repository))->handle(new GetMediaQuery($id->value()));

    expect($response->id)->toBe($id->value())
        ->and($response->url)->toBe('/uploads/2026/09/tee.png');
});

it('fails when media does not exist', function () {
    (new GetMediaQueryHandler(new InMemoryMediaRepository()))->handle(
        new GetMediaQuery('aa0e8400-e29b-41d4-a716-446655440000'),
    );
})->throws(MediaNotFound::class);
