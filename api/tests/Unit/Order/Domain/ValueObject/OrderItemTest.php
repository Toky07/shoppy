<?php

declare(strict_types=1);

use App\Order\Domain\ValueObject\CatalogProductId;
use App\Order\Domain\ValueObject\OrderItem;
use App\Order\Domain\ValueObject\OrderedProductName;
use App\Order\Domain\ValueObject\Quantity;
use App\Order\Domain\ValueObject\UnitPrice;

it('snapshots catalog identity, name, unit price and quantity', function () {
    $productId = CatalogProductId::fromString('550e8400-e29b-41d4-a716-446655440000');
    $name = OrderedProductName::fromString('Nuvora Tee');
    $unitPrice = UnitPrice::fromCents(1999);
    $quantity = Quantity::fromInt(2);

    $item = OrderItem::of($productId, $name, $unitPrice, $quantity);

    expect($item->catalogProductId())->toBe($productId)
        ->and($item->name())->toBe($name)
        ->and($item->unitPrice())->toBe($unitPrice)
        ->and($item->quantity())->toBe($quantity)
        ->and($item->lineTotalCents())->toBe(3998);
});
