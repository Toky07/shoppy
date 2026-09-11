<?php

declare(strict_types=1);

use App\Order\Domain\Exception\InvalidOrderId;
use App\Order\Domain\ValueObject\OrderId;

it('rejects an invalid uuid', function () {
    OrderId::fromString('not-a-uuid');
})->throws(InvalidOrderId::class);

it('generates a valid uuid', function () {
    $id = OrderId::generate();

    expect($id->value())->toMatch('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i');
});
