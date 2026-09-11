<?php

declare(strict_types=1);

use App\Order\Domain\Exception\InvalidCatalogProductId;
use App\Order\Domain\ValueObject\CatalogProductId;

it('rejects an invalid uuid', function () {
    CatalogProductId::fromString('not-a-uuid');
})->throws(InvalidCatalogProductId::class);

it('exposes the catalog product id it was created with', function () {
    $id = CatalogProductId::fromString('550e8400-e29b-41d4-a716-446655440000');

    expect($id->value())->toBe('550e8400-e29b-41d4-a716-446655440000');
});
