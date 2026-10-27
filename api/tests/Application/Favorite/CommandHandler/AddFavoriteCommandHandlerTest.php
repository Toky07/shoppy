<?php

declare(strict_types=1);

use App\Favorite\Application\Catalog;
use App\Favorite\Application\Command\AddFavoriteCommand;
use App\Favorite\Application\Command\ClearFavoritesCommand;
use App\Favorite\Application\Command\RemoveFavoriteCommand;
use App\Favorite\Application\CommandHandler\AddFavoriteCommandHandler;
use App\Favorite\Application\CommandHandler\ClearFavoritesCommandHandler;
use App\Favorite\Application\CommandHandler\RemoveFavoriteCommandHandler;
use App\Favorite\Application\Query\GetFavoritesQuery;
use App\Favorite\Application\QueryHandler\GetFavoritesQueryHandler;
use App\Favorite\Domain\Exception\FavoriteProductNotFound;
use App\Favorite\Infrastructure\Persistence\InMemoryFavoriteListRepository;
use App\Tests\Doubles\FakeFavoriteCatalog;

function favoriteCatalog(string ...$ids): Catalog
{
    $catalog = new FakeFavoriteCatalog();

    foreach ($ids as $id) {
        $catalog->publish($id);
    }

    return $catalog;
}

it('adds a published product to the customer list', function () {
    $lists = new InMemoryFavoriteListRepository();
    $productId = '550e8400-e29b-41d4-a716-446655440000';
    $secondId = '550e8400-e29b-41d4-a716-446655440001';
    $customerId = '11111111-1111-4111-8111-111111111111';
    $catalog = favoriteCatalog($productId, $secondId);

    (new AddFavoriteCommandHandler($lists, $catalog))->handle(new AddFavoriteCommand(
        $customerId,
        $productId,
    ));
    (new AddFavoriteCommandHandler($lists, $catalog))->handle(new AddFavoriteCommand(
        $customerId,
        $secondId,
    ));

    $response = (new GetFavoritesQueryHandler($lists))->handle(new GetFavoritesQuery($customerId));

    expect($response->toArray())->toBe([
        'productIds' => [
            '550e8400-e29b-41d4-a716-446655440001',
            $productId,
        ],
    ]);
});

it('rejects an unpublished product', function () {
    $lists = new InMemoryFavoriteListRepository();

    (new AddFavoriteCommandHandler($lists, favoriteCatalog()))->handle(new AddFavoriteCommand(
        '11111111-1111-4111-8111-111111111111',
        '550e8400-e29b-41d4-a716-446655440000',
    ));
})->throws(FavoriteProductNotFound::class);

it('removes and clears favorites', function () {
    $lists = new InMemoryFavoriteListRepository();
    $customerId = '11111111-1111-4111-8111-111111111111';
    $first = '550e8400-e29b-41d4-a716-446655440000';
    $second = '550e8400-e29b-41d4-a716-446655440001';
    $catalog = favoriteCatalog($first, $second);
    $add = new AddFavoriteCommandHandler($lists, $catalog);

    $add->handle(new AddFavoriteCommand($customerId, $first));
    $add->handle(new AddFavoriteCommand($customerId, $second));

    (new RemoveFavoriteCommandHandler($lists))->handle(new RemoveFavoriteCommand($customerId, $first));

    $afterRemove = (new GetFavoritesQueryHandler($lists))->handle(new GetFavoritesQuery($customerId));
    expect($afterRemove->productIds)->toBe([$second]);

    (new ClearFavoritesCommandHandler($lists))->handle(new ClearFavoritesCommand($customerId));

    $cleared = (new GetFavoritesQueryHandler($lists))->handle(new GetFavoritesQuery($customerId));
    expect($cleared->productIds)->toBe([]);
});

it('returns an empty list when the customer has no favorites yet', function () {
    $response = (new GetFavoritesQueryHandler(new InMemoryFavoriteListRepository()))
        ->handle(new GetFavoritesQuery('11111111-1111-4111-8111-111111111111'));

    expect($response->toArray())->toBe(['productIds' => []]);
});
