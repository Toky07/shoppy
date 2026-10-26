<?php

declare(strict_types=1);

use App\Cart\Application\AvailableStock;
use App\Cart\Application\Query\GetCartQuery;
use App\Cart\Application\QueryHandler\GetCartQueryHandler;
use App\Cart\Application\Response\CatalogSnapshot;
use App\Cart\Domain\Entity\Cart;
use App\Cart\Domain\ValueObject\CartId;
use App\Cart\Domain\ValueObject\CartProductId;
use App\Cart\Domain\ValueObject\CartQuantity;
use App\Cart\Domain\ValueObject\CustomerId;
use App\Cart\Infrastructure\Persistence\InMemoryCartRepository;
use App\Tests\Doubles\FakeCartCatalog;
use App\Tests\Doubles\RecordingCartCatalog;

it('loads catalog snapshots and reservations in one pass for every cart line', function () {
    $carts = new InMemoryCartRepository();
    $catalog = new FakeCartCatalog();
    $catalog->add(new CatalogSnapshot(
        id: '550e8400-e29b-41d4-a716-446655440000',
        name: 'Nuvora Tee',
        unitPriceCents: 1999,
        stock: 10,
        imageUrl: '/uploads/tee.png',
    ));
    $catalog->add(new CatalogSnapshot(
        id: '550e8400-e29b-41d4-a716-446655440001',
        name: 'Nuvora Mug',
        unitPriceCents: 1299,
        stock: 5,
        imageUrl: '/uploads/mug.png',
    ));
    $recording = new RecordingCartCatalog($catalog);
    $now = new DateTimeImmutable('2026-08-20T12:00:00+00:00');
    $cart = Cart::create(
        CartId::fromString('bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb'),
        CustomerId::fromString('11111111-1111-4111-8111-111111111111'),
        $now,
    );
    $cart->addItem(
        CartProductId::fromString('550e8400-e29b-41d4-a716-446655440000'),
        CartQuantity::fromInt(2),
        $now,
    );
    $cart->addItem(
        CartProductId::fromString('550e8400-e29b-41d4-a716-446655440001'),
        CartQuantity::fromInt(1),
        $now,
    );
    $carts->save($cart);

    $response = (new GetCartQueryHandler($carts, $recording, new AvailableStock($carts, $recording)))
        ->handle(new GetCartQuery('11111111-1111-4111-8111-111111111111'));

    expect($recording->findByItemsCalls)->toBe(1)
        ->and($recording->findByIdCalls)->toBe(0)
        ->and($response->items)->toHaveCount(2)
        ->and($response->items[0]->toArray()['imageUrl'])->toBe('/uploads/tee.png')
        ->and($response->items[1]->toArray()['imageUrl'])->toBe('/uploads/mug.png')
        ->and($response->items[0]->toArray()['availableStock'])->toBe(10)
        ->and($response->items[1]->toArray()['availableStock'])->toBe(5);
});
