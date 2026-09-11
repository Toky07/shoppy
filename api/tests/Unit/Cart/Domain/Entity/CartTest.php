<?php

declare(strict_types=1);

use App\Cart\Domain\Entity\Cart;
use App\Cart\Domain\Exception\CartItemNotFound;
use App\Cart\Domain\Exception\EmptyCart;
use App\Cart\Domain\Exception\InvalidCartQuantity;
use App\Cart\Domain\ValueObject\CartId;
use App\Cart\Domain\ValueObject\CartProductId;
use App\Cart\Domain\ValueObject\CartQuantity;
use App\Cart\Domain\ValueObject\CustomerId;

function emptyCart(): Cart
{
    return Cart::create(
        CartId::fromString('bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb'),
        CustomerId::fromString('11111111-1111-4111-8111-111111111111'),
        new DateTimeImmutable('2026-08-20T12:00:00+00:00'),
    );
}

it('creates an empty cart', function () {
    $cart = emptyCart();

    expect($cart->isEmpty())->toBeTrue()
        ->and($cart->items())->toBe([]);
});

it('adds an item to the cart', function () {
    $cart = emptyCart();
    $now = new DateTimeImmutable('2026-08-20T13:00:00+00:00');

    $cart->addItem(
        CartProductId::fromString('550e8400-e29b-41d4-a716-446655440000'),
        CartQuantity::fromInt(2),
        $now,
    );

    expect($cart->items())->toHaveCount(1)
        ->and($cart->items()[0]->productId()->value())->toBe('550e8400-e29b-41d4-a716-446655440000')
        ->and($cart->items()[0]->quantity()->value())->toBe(2)
        ->and($cart->updatedAt())->toBe($now);
});

it('merges quantities when adding the same product', function () {
    $cart = emptyCart();
    $productId = CartProductId::fromString('550e8400-e29b-41d4-a716-446655440000');

    $cart->addItem($productId, CartQuantity::fromInt(2), new DateTimeImmutable('2026-08-20T13:00:00+00:00'));
    $cart->addItem($productId, CartQuantity::fromInt(3), new DateTimeImmutable('2026-08-20T14:00:00+00:00'));

    expect($cart->items())->toHaveCount(1)
        ->and($cart->items()[0]->quantity()->value())->toBe(5);
});

it('sets an item quantity', function () {
    $cart = emptyCart();
    $productId = CartProductId::fromString('550e8400-e29b-41d4-a716-446655440000');
    $cart->addItem($productId, CartQuantity::fromInt(2), new DateTimeImmutable('2026-08-20T13:00:00+00:00'));

    $cart->setItemQuantity($productId, CartQuantity::fromInt(7), new DateTimeImmutable('2026-08-20T14:00:00+00:00'));

    expect($cart->items()[0]->quantity()->value())->toBe(7);
});

it('removes an item from the cart', function () {
    $cart = emptyCart();
    $productId = CartProductId::fromString('550e8400-e29b-41d4-a716-446655440000');
    $cart->addItem($productId, CartQuantity::fromInt(2), new DateTimeImmutable('2026-08-20T13:00:00+00:00'));

    $cart->removeItem($productId, new DateTimeImmutable('2026-08-20T14:00:00+00:00'));

    expect($cart->isEmpty())->toBeTrue();
});

it('clears the cart', function () {
    $cart = emptyCart();
    $cart->addItem(
        CartProductId::fromString('550e8400-e29b-41d4-a716-446655440000'),
        CartQuantity::fromInt(2),
        new DateTimeImmutable('2026-08-20T13:00:00+00:00'),
    );

    $cart->clear(new DateTimeImmutable('2026-08-20T14:00:00+00:00'));

    expect($cart->isEmpty())->toBeTrue();
});

it('rejects removing a missing item', function () {
    emptyCart()->removeItem(
        CartProductId::fromString('550e8400-e29b-41d4-a716-446655440000'),
        new DateTimeImmutable('2026-08-20T13:00:00+00:00'),
    );
})->throws(CartItemNotFound::class);

it('rejects an empty cart checkout assertion', function () {
    emptyCart()->assertNotEmpty();
})->throws(EmptyCart::class);

it('rejects a quantity below one', function () {
    CartQuantity::fromInt(0);
})->throws(InvalidCartQuantity::class);
