<?php

declare(strict_types=1);

namespace App\Invoice\Application\EventSubscriber;

use App\Invoice\Domain\Entity\Invoice;
use App\Invoice\Domain\Repository\InvoiceOrderSnapshotRepository;
use App\Invoice\Domain\Repository\InvoiceRepository;
use App\Invoice\Domain\ValueObject\CustomerReference;
use App\Invoice\Domain\ValueObject\InvoiceId;
use App\Invoice\Domain\ValueObject\MoneyAmount;
use App\Invoice\Domain\ValueObject\OrderReference;
use App\Invoice\Domain\ValueObject\PaymentReference;
use App\Shared\Application\Event\InvoiceIssued;
use App\Shared\Application\Event\PaymentCompleted;
use App\Shared\Domain\Clock;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final readonly class CreateInvoiceOnPaymentCompleted implements EventSubscriberInterface
{
    public function __construct(
        private InvoiceRepository $invoices,
        private Clock $clock,
        private EventDispatcherInterface $eventDispatcher,
        private InvoiceOrderSnapshotRepository $snapshots,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [PaymentCompleted::class => 'onPaymentCompleted'];
    }

    public function onPaymentCompleted(PaymentCompleted $event): void
    {
        $orderId = OrderReference::fromString($event->orderId);

        if ($this->invoices->findByOrderId($orderId) !== null) {
            return;
        }

        $issuedAt = $this->clock->now();
        $invoice = Invoice::issue(
            InvoiceId::generate(),
            $this->invoices->nextNumber($issuedAt),
            $orderId,
            CustomerReference::fromString($event->customerId),
            PaymentReference::fromString($event->paymentId),
            MoneyAmount::fromCents($event->amountCents),
            $issuedAt,
            $this->snapshots->findByOrderId($orderId)?->lines() ?? [],
        );

        $this->invoices->save($invoice);

        $this->eventDispatcher->dispatch(new InvoiceIssued(
            invoiceId: $invoice->id()->value(),
            number: $invoice->number()->value(),
            orderId: $invoice->orderId()->value(),
            customerId: $invoice->customerId()->value(),
            paymentId: $invoice->paymentId()->value(),
            amountCents: $invoice->amount()->cents(),
        ));
    }
}
