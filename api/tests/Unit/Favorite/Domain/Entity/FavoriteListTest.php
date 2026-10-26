<?php

declare(strict_types=1);

use App\Favorite\Domain\Entity\FavoriteList;
use App\Favorite\Domain\ValueObject\CustomerId;
use App\Favorite\Domain\ValueObject\FavoriteProductId;

function emptyFavorites(): FavoriteList
{
    return FavoriteList::empty(CustomerId::fromString('11111111-1111-4111-8111-111111111111'));
}

function product(string $suffix): FavoriteProductId
{
    return FavoriteProductId::fromString('550e8400-e29b-41d4-a716-44665544000'.$suffix);
}

it('starts empty', function () {
    $list = emptyFavorites();

    expect($list->productIds())->toBe([])
        ->and($list->contains(product('0')))->toBeFalse();
});

it('prepends a product and ignores a duplicate', function () {
    $list = emptyFavorites();

    $list->add(product('0'));
    $list->add(product('1'));
    $list->add(product('0'));

    expect(array_map(
        static fn (FavoriteProductId $id): string => $id->value(),
        $list->productIds(),
    ))->toBe([
        '550e8400-e29b-41d4-a716-446655440001',
        '550e8400-e29b-41d4-a716-446655440000',
    ]);
});

it('removes a product without failing when it is missing', function () {
    $list = emptyFavorites();
    $list->add(product('0'));
    $list->remove(product('1'));
    $list->remove(product('0'));

    expect($list->productIds())->toBe([]);
});

it('clears every product', function () {
    $list = emptyFavorites();
    $list->add(product('0'));
    $list->add(product('1'));
    $list->clear();

    expect($list->productIds())->toBe([]);
});

it('merges guest ids first then keeps the remaining account ids', function () {
    $list = emptyFavorites();
    $list->add(product('2'));
    $list->add(product('1'));

    $list->merge([product('0'), product('1'), product('0')]);

    expect(array_map(
        static fn (FavoriteProductId $id): string => $id->value(),
        $list->productIds(),
    ))->toBe([
        '550e8400-e29b-41d4-a716-446655440000',
        '550e8400-e29b-41d4-a716-446655440001',
        '550e8400-e29b-41d4-a716-446655440002',
    ]);
});
