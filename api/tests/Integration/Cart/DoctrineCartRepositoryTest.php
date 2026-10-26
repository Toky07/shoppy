<?php

declare(strict_types=1);

use App\Cart\Domain\Exception\CartAlreadyCheckingOut;
use App\Cart\Domain\Entity\Cart;
use App\Cart\Domain\Repository\CartRepository;
use App\Cart\Domain\ValueObject\CartId;
use App\Cart\Domain\ValueObject\CartItem;
use App\Cart\Domain\ValueObject\CartProductId;
use App\Cart\Domain\ValueObject\CartQuantity;
use App\Cart\Domain\ValueObject\CustomerId;
use Doctrine\ORM\EntityManagerInterface;

it('persists a cart and its items by customer', function () {
    $customerId = CustomerId::fromString('11111111-1111-4111-8111-111111111111');
    $cart = Cart::create(
        CartId::fromString('bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb'),
        $customerId,
        new DateTimeImmutable('2026-08-20T12:00:00+00:00'),
    );
    $cart->addItem(
        CartProductId::fromString('550e8400-e29b-41d4-a716-446655440000'),
        CartQuantity::fromInt(2),
        new DateTimeImmutable('2026-08-20T13:00:00+00:00'),
    );

    $repository = self::getContainer()->get(CartRepository::class);
    $entityManager = self::getContainer()->get(EntityManagerInterface::class);

    $repository->save($cart);
    $entityManager->clear();

    $found = $repository->findByCustomerId($customerId);

    expect($found)->not->toBeNull()
        ->and($found->id()->value())->toBe('bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb')
        ->and($found->items())->toHaveCount(1)
        ->and($found->items()[0]->productId()->value())->toBe('550e8400-e29b-41d4-a716-446655440000')
        ->and($found->items()[0]->quantity()->value())->toBe(2);

    $found->addItem(
        CartProductId::fromString('550e8400-e29b-41d4-a716-446655440001'),
        CartQuantity::fromInt(1),
        new DateTimeImmutable('2026-08-20T14:00:00+00:00'),
    );
    $repository->save($found);
    $entityManager->clear();

    $updated = $repository->findByCustomerId($customerId);

    expect($updated)->not->toBeNull()
        ->and($updated->items())->toHaveCount(2);
});

it('lets only one checkout claim the same cart version', function () {
    $customerId = CustomerId::fromString('11111111-1111-4111-8111-111111111111');
    $cart = Cart::create(
        CartId::fromString('bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb'),
        $customerId,
        new DateTimeImmutable('2026-08-20T12:00:00+00:00'),
    );
    $cart->addItem(
        CartProductId::fromString('550e8400-e29b-41d4-a716-446655440000'),
        CartQuantity::fromInt(2),
        new DateTimeImmutable('2026-08-20T13:00:00+00:00'),
    );

    $repository = self::getContainer()->get(CartRepository::class);
    $entityManager = self::getContainer()->get(EntityManagerInterface::class);
    $repository->save($cart);
    $entityManager->clear();

    $first = $repository->findByCustomerId($customerId);
    $entityManager->clear();
    $second = $repository->findByCustomerId($customerId);

    $repository->claimForCheckout($first);

    expect(fn () => $repository->claimForCheckout($second))->toThrow(CartAlreadyCheckingOut::class)
        ->and($first->version())->toBe(2)
        ->and($repository->findByCustomerId($customerId)?->items())->toHaveCount(1);
});

it('sums other customers reservations for several lines in one query', function () {
    $now = new DateTimeImmutable('2026-08-20T12:00:00+00:00');
    $tee = CartProductId::fromString('550e8400-e29b-41d4-a716-446655440000');
    $mug = CartProductId::fromString('550e8400-e29b-41d4-a716-446655440001');
    $alice = CustomerId::fromString('11111111-1111-4111-8111-111111111111');
    $bob = CustomerId::fromString('22222222-2222-4222-8222-222222222222');

    $alicesCart = Cart::create(CartId::fromString('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa'), $alice, $now);
    $alicesCart->addItem($tee, CartQuantity::fromInt(1), $now);
    $alicesCart->addItem($mug, CartQuantity::fromInt(2), $now);

    $bobsCart = Cart::create(CartId::fromString('bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb'), $bob, $now);
    $bobsCart->addItem($tee, CartQuantity::fromInt(3), $now);
    $bobsCart->addItem($mug, CartQuantity::fromInt(4), $now);

    $repository = self::getContainer()->get(CartRepository::class);
    $repository->save($alicesCart);
    $repository->save($bobsCart);

    $reserved = $repository->reservedQuantities($alicesCart->items(), $alice);

    expect($reserved)->toBe([
        CartItem::of($tee, CartQuantity::fromInt(1))->lineKey() => 3,
        CartItem::of($mug, CartQuantity::fromInt(1))->lineKey() => 4,
    ]);
});
