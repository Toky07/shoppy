<?php

declare(strict_types=1);

use App\Order\Domain\Exception\InvalidOrderStatus;
use App\Order\Domain\ValueObject\OrderStatus;

it('exposes the pending status', function () {
    expect(OrderStatus::pending()->value())->toBe('pending');
});

it('exposes the cancelled and paid statuses', function () {
    expect(OrderStatus::cancelled()->value())->toBe('cancelled')
        ->and(OrderStatus::paid()->value())->toBe('paid');
});

it('creates a status from a valid value', function () {
    expect(OrderStatus::fromString('pending'))->toEqual(OrderStatus::pending())
        ->and(OrderStatus::fromString('cancelled'))->toEqual(OrderStatus::cancelled())
        ->and(OrderStatus::fromString('paid'))->toEqual(OrderStatus::paid());
});

it('rejects an unknown status', function () {
    OrderStatus::fromString('shipped');
})->throws(InvalidOrderStatus::class);
