<?php

declare(strict_types=1);

namespace App\Invoice\Application\EventSubscriber;

use App\Invoice\Domain\Entity\InvoiceOrderSnapshot;
use App\Invoice\Domain\Repository\InvoiceOrderSnapshotRepository;
use App\Invoice\Domain\ValueObject\CustomerReference;
use App\Invoice\Domain\ValueObject\InvoiceLine;
use App\Invoice\Domain\ValueObject\OrderReference;
use App\Shared\Application\Event\OrderPlaced;
use App\Shared\Application\Event\OrderPlacedItem;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final readonly class CaptureInvoiceDetailsOnOrderPlaced implements EventSubscriberInterface
{
    public function __construct(private InvoiceOrderSnapshotRepository $snapshots)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [OrderPlaced::class => 'onOrderPlaced'];
    }

    public function onOrderPlaced(OrderPlaced $event): void
    {
        $lines = array_map(
            static fn (OrderPlacedItem $item): InvoiceLine => InvoiceLine::of(
                $item->description,
                $item->quantity,
                $item->unitPriceCents,
            ),
            $event->items,
        );

        if ($event->shippingLabel !== null && $event->shippingLabel !== '') {
            $lines[] = InvoiceLine::of('Livraison '.$event->shippingLabel, 1, $event->shippingCents);
        }

        $this->snapshots->save(new InvoiceOrderSnapshot(
            OrderReference::fromString($event->orderId),
            CustomerReference::fromString($event->customerId),
            $lines,
        ));
    }
}
