<?php

declare(strict_types=1);

use App\Order\Application\Command\MarkOrderPaidCommand;
use App\Order\Application\CommandHandler\MarkOrderPaidCommandHandler;
use App\Order\Domain\Entity\Order;
use App\Order\Domain\ValueObject\CatalogProductId;
use App\Order\Domain\ValueObject\CustomerId;
use App\Order\Domain\ValueObject\OrderId;
use App\Order\Domain\ValueObject\OrderItem;
use App\Order\Domain\ValueObject\OrderedProductName;
use App\Order\Domain\ValueObject\Quantity;
use App\Order\Domain\ValueObject\UnitPrice;
use App\Order\Infrastructure\Persistence\InMemoryOrderRepository;
use App\Product\Application\Command\CreateProductCommand;
use App\Product\Application\Command\SetProductStockCommand;
use App\Product\Application\Command\UpdateProductCommand;
use App\Product\Application\CommandHandler\SetProductStockCommandHandler;
use App\Product\Infrastructure\Persistence\InMemoryProductRepository;
use App\Tests\Doubles\FixedClock;
use App\Tests\Doubles\InMemoryAuditLogger;
use App\User\Application\Command\AssignUserRoleCommand;
use App\User\Application\CommandHandler\AssignUserRoleCommandHandler;
use App\User\Domain\Entity\User;
use App\User\Domain\ValueObject\Email;
use App\User\Domain\ValueObject\UserId;
use App\User\Infrastructure\Persistence\InMemoryUserRepository;
use Symfony\Component\EventDispatcher\EventDispatcher;

it('records who changed a role, a price, a stock and a payment', function () {
    $audit = new InMemoryAuditLogger();
    $now = new DateTimeImmutable('2026-08-20T12:00:00+00:00');

    $users = new InMemoryUserRepository();
    $user = User::register(
        UserId::fromString('11111111-1111-4111-8111-111111111111'),
        Email::fromString('ada@nuvora.test'),
        $now,
    );
    $users->save($user);
    (new AssignUserRoleCommandHandler($users, null, $audit))->handle(new AssignUserRoleCommand(
        id: $user->id()->value(),
        role: 'admin',
    ));

    $products = new InMemoryProductRepository();
    $productId = createProducts($products, new FixedClock($now))->handle(new CreateProductCommand(
        name: 'Nuvora Tee',
        priceCents: 1999,
        stock: 10,
    ));
    updateProducts($products, audit: $audit)->handle(new UpdateProductCommand(
        id: $productId->value(),
        priceCents: 2499,
    ));
    (new SetProductStockCommandHandler($products, $audit))->handle(new SetProductStockCommand(
        id: $productId->value(),
        stock: 4,
    ));

    $orders = new InMemoryOrderRepository();
    $orderId = 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa';
    $orders->save(Order::place(
        OrderId::fromString($orderId),
        CustomerId::fromString('11111111-1111-4111-8111-111111111111'),
        [OrderItem::of(
            CatalogProductId::fromString($productId->value()),
            OrderedProductName::fromString('Nuvora Tee'),
            UnitPrice::fromCents(1999),
            Quantity::fromInt(1),
        )],
        $now,
        samplePostalAddress(),
        samplePostalAddress(),
        sampleShippingMethod(),
    ));
    (new MarkOrderPaidCommandHandler($orders, new EventDispatcher(), $audit))->handle(
        new MarkOrderPaidCommand($orderId),
    );

    expect($audit->entries)->toEqual([
        [
            'action' => 'role_changed',
            'subjectType' => 'user',
            'subjectId' => $user->id()->value(),
            'metadata' => ['from' => 'customer', 'to' => 'admin', 'label' => 'ada@nuvora.test'],
        ],
        [
            'action' => 'price_changed',
            'subjectType' => 'product',
            'subjectId' => $productId->value(),
            'metadata' => ['fromCents' => 1999, 'toCents' => 2499, 'label' => 'Nuvora Tee'],
        ],
        [
            'action' => 'stock_changed',
            'subjectType' => 'product',
            'subjectId' => $productId->value(),
            'metadata' => ['from' => 10, 'to' => 4, 'label' => 'Nuvora Tee'],
        ],
        [
            'action' => 'payment_changed',
            'subjectType' => 'order',
            'subjectId' => $orderId,
            'metadata' => ['from' => 'pending', 'to' => 'paid', 'label' => 'Nuvora Tee'],
        ],
    ]);
});

it('skips an unchanged role, price or stock', function () {
    $audit = new InMemoryAuditLogger();
    $now = new DateTimeImmutable('2026-08-20T12:00:00+00:00');
    $users = new InMemoryUserRepository();
    $user = User::register(
        UserId::fromString('11111111-1111-4111-8111-111111111111'),
        Email::fromString('ada@nuvora.test'),
        $now,
    );
    $users->save($user);
    (new AssignUserRoleCommandHandler($users, null, $audit))->handle(new AssignUserRoleCommand(
        id: $user->id()->value(),
        role: 'customer',
    ));

    $products = new InMemoryProductRepository();
    $productId = createProducts($products, new FixedClock($now))->handle(new CreateProductCommand(
        name: 'Nuvora Tee',
        priceCents: 1999,
        stock: 10,
    ));
    updateProducts($products, audit: $audit)->handle(new UpdateProductCommand(
        id: $productId->value(),
        priceCents: 1999,
        name: 'Nuvora Tee 2',
    ));
    (new SetProductStockCommandHandler($products, $audit))->handle(new SetProductStockCommand(
        id: $productId->value(),
        stock: 10,
    ));

    expect($audit->entries)->toBe([]);
});
