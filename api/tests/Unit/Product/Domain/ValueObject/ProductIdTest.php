<?php

declare(strict_types=1);

use App\Product\Domain\Exception\InvalidProductId;
use App\Product\Domain\ValueObject\ProductId;

it('rejects an invalid uuid', function () {
    ProductId::fromString('not-a-uuid');
})->throws(InvalidProductId::class);

it('generates a valid uuid', function () {
    $id = ProductId::generate();

    expect($id->value())->toMatch('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i');
});
