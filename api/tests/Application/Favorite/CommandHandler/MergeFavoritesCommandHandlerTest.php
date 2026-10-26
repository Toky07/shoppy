<?php

declare(strict_types=1);

use App\Favorite\Application\Command\AddFavoriteCommand;
use App\Favorite\Application\Command\MergeFavoritesCommand;
use App\Favorite\Application\CommandHandler\AddFavoriteCommandHandler;
use App\Favorite\Application\CommandHandler\MergeFavoritesCommandHandler;
use App\Favorite\Application\Query\GetFavoritesQuery;
use App\Favorite\Application\QueryHandler\GetFavoritesQueryHandler;
use App\Favorite\Infrastructure\Persistence\InMemoryFavoriteListRepository;
use App\Tests\Doubles\FakeFavoriteCatalog;

it('merges guest ids in one catalog pass and skips unpublished products', function () {
    $lists = new InMemoryFavoriteListRepository();
    $catalog = new FakeFavoriteCatalog();
    $account = '550e8400-e29b-41d4-a716-446655440002';
    $guest = '550e8400-e29b-41d4-a716-446655440000';
    $shared = '550e8400-e29b-41d4-a716-446655440001';
    $catalog->publish($account);
    $catalog->publish($guest);
    $catalog->publish($shared);
    $customerId = '11111111-1111-4111-8111-111111111111';

    (new AddFavoriteCommandHandler($lists, $catalog))->handle(new AddFavoriteCommand($customerId, $account));
    (new AddFavoriteCommandHandler($lists, $catalog))->handle(new AddFavoriteCommand($customerId, $shared));

    (new MergeFavoritesCommandHandler($lists, $catalog))->handle(new MergeFavoritesCommand($customerId, [
        $guest,
        $shared,
        '00000000-0000-4000-8000-000000000000',
    ]));

    $response = (new GetFavoritesQueryHandler($lists))->handle(new GetFavoritesQuery($customerId));

    expect($catalog->publishedAmongCalls)->toBe(1)
        ->and($response->productIds)->toBe([$guest, $shared, $account]);
});

it('does nothing when every guest id is unpublished', function () {
    $lists = new InMemoryFavoriteListRepository();
    $catalog = new FakeFavoriteCatalog();

    (new MergeFavoritesCommandHandler($lists, $catalog))->handle(new MergeFavoritesCommand(
        '11111111-1111-4111-8111-111111111111',
        ['550e8400-e29b-41d4-a716-446655440000'],
    ));

    $response = (new GetFavoritesQueryHandler($lists))->handle(new GetFavoritesQuery(
        '11111111-1111-4111-8111-111111111111',
    ));

    expect($response->productIds)->toBe([])
        ->and($lists->saveCalls)->toBe(0);
});
