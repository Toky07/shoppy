<?php

declare(strict_types=1);

use App\Product\Domain\Exception\InvalidProductSearch;
use App\Product\Domain\Exception\InvalidProductSort;
use App\Product\Domain\ValueObject\ProductListCriteria;
use App\Product\Domain\ValueObject\ProductSort;

it('defaults to newest without a search', function () {
    $criteria = ProductListCriteria::default();

    expect($criteria->search)->toBeNull()
        ->and($criteria->sort->value())->toBe(ProductSort::NEWEST)
        ->and($criteria->hasSearch())->toBeFalse();
});

it('trims an empty search to null', function () {
    $criteria = ProductListCriteria::fromInput('   ', null);

    expect($criteria->search)->toBeNull()
        ->and($criteria->sort->value())->toBe(ProductSort::NEWEST);
});

it('keeps a trimmed search and parsed sort', function () {
    $criteria = ProductListCriteria::fromInput('  hoodie  ', 'price_desc');

    expect($criteria->search)->toBe('hoodie')
        ->and($criteria->sort->value())->toBe(ProductSort::PRICE_DESC)
        ->and($criteria->hasSearch())->toBeTrue();
});

it('rejects an unknown sort', function () {
    ProductSort::fromString('popularity');
})->throws(InvalidProductSort::class);

it('rejects a search that is too long', function () {
    ProductListCriteria::fromInput(str_repeat('a', 101), null);
})->throws(InvalidProductSearch::class);
