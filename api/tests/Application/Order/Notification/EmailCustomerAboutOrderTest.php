<?php

declare(strict_types=1);

use App\Email\Domain\Event\EmailRequested;
use App\Order\Application\Port\CustomerContact;
use App\Order\Domain\Entity\Order;
use App\Order\Domain\ValueObject\CatalogProductId;
use App\Order\Domain\ValueObject\CustomerId;
use App\Order\Domain\ValueObject\OrderId;
use App\Order\Domain\ValueObject\OrderItem;
use App\Order\Domain\ValueObject\OrderedProductName;
use App\Order\Domain\ValueObject\Quantity;
use App\Order\Domain\ValueObject\UnitPrice;
use App\Order\Infrastructure\Notification\EmailCustomerAboutOrder;
use App\Order\Infrastructure\Persistence\InMemoryOrderRepository;
use App\Shared\Application\Event\OrderCancelled;
use App\Shared\Application\Event\OrderPlaced;
use App\Tests\Doubles\RecordingEventBus;

function orderMailFixture(): array
{
    $orderId = 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa';
    $customerId = '11111111-1111-4111-8111-111111111111';
    $order = Order::place(
        OrderId::fromString($orderId),
        CustomerId::fromString($customerId),
        [OrderItem::of(
            CatalogProductId::fromString('550e8400-e29b-41d4-a716-446655440000'),
            OrderedProductName::fromString('Nuvora Tee'),
            UnitPrice::fromCents(1999),
            Quantity::fromInt(2),
        )],
        new DateTimeImmutable('2026-08-20T12:00:00+00:00'),
    samplePostalAddress(), samplePostalAddress(), sampleShippingMethod());

    return [$orderId, $customerId, $order];
}

it('sends an order confirmation with a receipt attachment', function () {
    [$orderId, $customerId, $order] = orderMailFixture();
    $orders = new InMemoryOrderRepository();
    $orders->save($order);
    $events = new RecordingEventBus();
    $notifier = new EmailCustomerAboutOrder($orders, new class implements CustomerContact {
        public function emailFor(CustomerId $customerId): ?string
        {
            return 'ada@shoppy.test';
        }
    }, $events);

    $notifier->onOrderPlaced(new OrderPlaced($orderId, $customerId, 3998));

    expect($events->published)->toHaveCount(1)
        ->and($events->published[0])->toBeInstanceOf(EmailRequested::class)
        ->and($events->published[0]->to->value())->toBe('ada@shoppy.test')
        ->and($events->published[0]->subject->value())->toBe('Commande confirmée')
        ->and($events->published[0]->body->text())->toBe('Votre commande aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa est enregistrée. Total : 39,98 €.')
        ->and($events->published[0]->attachments)->toHaveCount(1)
        ->and($events->published[0]->attachments[0]->filename())->toBe('recu.txt')
        ->and($events->published[0]->attachments[0]->mimeType())->toBe('text/plain')
        ->and($events->published[0]->attachments[0]->content())->toBe(implode("\n", [
            'Commande aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
            'Nuvora Tee x2 — 39,98 €',
            'Total : 39,98 €',
            'Expédition : Standard, offerte',
            'Livraison : Ada Lovelace, 10 rue de la Paix, 75002 Paris, FR',
            'Facturation : Ada Lovelace, 10 rue de la Paix, 75002 Paris, FR',
        ]));
});

it('sends a cancellation without an attachment', function () {
    [$orderId, $customerId, $order] = orderMailFixture();
    $orders = new InMemoryOrderRepository();
    $orders->save($order);
    $events = new RecordingEventBus();
    $notifier = new EmailCustomerAboutOrder($orders, new class implements CustomerContact {
        public function emailFor(CustomerId $customerId): ?string
        {
            return 'ada@shoppy.test';
        }
    }, $events);

    $notifier->onOrderCancelled(new OrderCancelled($orderId, $customerId));

    expect($events->published)->toHaveCount(1)
        ->and($events->published[0]->subject->value())->toBe('Commande annulée')
        ->and($events->published[0]->body->text())->toBe('Votre commande aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa a été annulée.')
        ->and($events->published[0]->attachments)->toBe([]);
});

it('skips the email when the customer has no address', function () {
    [$orderId, $customerId, $order] = orderMailFixture();
    $orders = new InMemoryOrderRepository();
    $orders->save($order);
    $events = new RecordingEventBus();
    $notifier = new EmailCustomerAboutOrder($orders, new class implements CustomerContact {
        public function emailFor(CustomerId $customerId): ?string
        {
            return null;
        }
    }, $events);

    $notifier->onOrderPlaced(new OrderPlaced($orderId, $customerId, 3998));

    expect($events->published)->toBe([]);
});
