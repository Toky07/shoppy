<?php

declare(strict_types=1);

use App\Invoice\Application\EventSubscriber\CaptureInvoiceDetailsOnOrderPlaced;
use App\Invoice\Application\EventSubscriber\CreateInvoiceOnPaymentCompleted;
use App\Invoice\Domain\ValueObject\OrderReference;
use App\Invoice\Domain\Entity\InvoiceBranding;
use App\Invoice\Domain\ValueObject\InvoiceLogo;
use App\Invoice\Infrastructure\Persistence\InMemoryInvoiceBrandingRepository;
use App\Invoice\Infrastructure\Persistence\InMemoryInvoiceOrderSnapshotRepository;
use App\Invoice\Infrastructure\Persistence\InMemoryInvoiceRepository;
use App\Shared\Application\Event\InvoiceIssued;
use App\Shared\Application\Event\OrderPlaced;
use App\Shared\Application\Event\OrderPlacedItem;
use App\Shared\Application\Event\PaymentCompleted;
use App\Tests\Doubles\FixedClock;
use Symfony\Component\EventDispatcher\EventDispatcher;

function invoicePaymentDispatcher(
    InMemoryInvoiceRepository $invoices = new InMemoryInvoiceRepository(),
    InMemoryInvoiceBrandingRepository $branding = new InMemoryInvoiceBrandingRepository(),
): EventDispatcher {
    $snapshots = new InMemoryInvoiceOrderSnapshotRepository();
    $dispatcher = new EventDispatcher();
    $dispatcher->addSubscriber(new CaptureInvoiceDetailsOnOrderPlaced($snapshots));
    $dispatcher->addSubscriber(new CreateInvoiceOnPaymentCompleted(
        $invoices,
        new FixedClock(new DateTimeImmutable('2026-08-20T12:05:00+00:00')),
        $dispatcher,
        $snapshots,
        $branding,
    ));

    return $dispatcher;
}

it('issues an invoice when a payment is completed', function () {
    $invoices = new InMemoryInvoiceRepository();
    $dispatcher = invoicePaymentDispatcher($invoices);
    $issued = [];
    $dispatcher->addListener(InvoiceIssued::class, static function (InvoiceIssued $event) use (&$issued): void {
        $issued[] = $event;
    });

    $dispatcher->dispatch(new PaymentCompleted(
        paymentId: 'dddddddd-dddd-4ddd-8ddd-dddddddddddd',
        orderId: 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
        customerId: '11111111-1111-4111-8111-111111111111',
        amountCents: 4488,
    ));

    $invoice = $invoices->findByOrderId(OrderReference::fromString('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa'));

    expect($invoice)->not->toBeNull()
        ->and($invoice->number()->value())->toBe('FAC-2026-0001')
        ->and($invoice->orderId()->value())->toBe('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa')
        ->and($invoice->customerId()->value())->toBe('11111111-1111-4111-8111-111111111111')
        ->and($invoice->paymentId()->value())->toBe('dddddddd-dddd-4ddd-8ddd-dddddddddddd')
        ->and($invoice->amount()->cents())->toBe(4488)
        ->and($invoice->lines())->toBe([])
        ->and($invoice->logo())->toBeNull()
        ->and($invoice->issuedAt()->format(DateTimeInterface::ATOM))->toBe('2026-08-20T12:05:00+00:00')
        ->and($issued)->toHaveCount(1)
        ->and($issued[0]->number)->toBe('FAC-2026-0001')
        ->and($issued[0]->orderId)->toBe('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa')
        ->and($issued[0]->amountCents)->toBe(4488);
});

it('does not issue a second invoice for the same order', function () {
    $invoices = new InMemoryInvoiceRepository();
    $dispatcher = invoicePaymentDispatcher($invoices);
    $issued = [];
    $dispatcher->addListener(InvoiceIssued::class, static function (InvoiceIssued $event) use (&$issued): void {
        $issued[] = $event;
    });
    $event = new PaymentCompleted(
        paymentId: 'dddddddd-dddd-4ddd-8ddd-dddddddddddd',
        orderId: 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
        customerId: '11111111-1111-4111-8111-111111111111',
        amountCents: 4488,
    );

    $dispatcher->dispatch($event);
    $dispatcher->dispatch($event);

    expect($invoices->all())->toHaveCount(1)
        ->and($issued)->toHaveCount(1);
});

it('numbers invoices sequentially in the same year', function () {
    $invoices = new InMemoryInvoiceRepository();
    $dispatcher = invoicePaymentDispatcher($invoices);

    $dispatcher->dispatch(new PaymentCompleted(
        paymentId: 'dddddddd-dddd-4ddd-8ddd-dddddddddddd',
        orderId: 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
        customerId: '11111111-1111-4111-8111-111111111111',
        amountCents: 1999,
    ));
    $dispatcher->dispatch(new PaymentCompleted(
        paymentId: 'eeeeeeee-eeee-4eee-8eee-eeeeeeeeeeee',
        orderId: 'bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb',
        customerId: '11111111-1111-4111-8111-111111111111',
        amountCents: 2489,
    ));

    $first = $invoices->findByOrderId(OrderReference::fromString('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa'));
    $second = $invoices->findByOrderId(OrderReference::fromString('bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb'));

    expect($first?->number()->value())->toBe('FAC-2026-0001')
        ->and($second?->number()->value())->toBe('FAC-2026-0002');
});

it('copies order lines and shipping onto the invoice when payment completes', function () {
    $invoices = new InMemoryInvoiceRepository();
    $dispatcher = invoicePaymentDispatcher($invoices);

    $dispatcher->dispatch(new OrderPlaced(
        orderId: 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
        customerId: '11111111-1111-4111-8111-111111111111',
        amountCents: 4488,
        items: [new OrderPlacedItem(description: 'Nuvora Tee', quantity: 2, unitPriceCents: 1999)],
        shippingLabel: 'Standard',
        shippingCents: 490,
    ));
    $dispatcher->dispatch(new PaymentCompleted(
        paymentId: 'dddddddd-dddd-4ddd-8ddd-dddddddddddd',
        orderId: 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
        customerId: '11111111-1111-4111-8111-111111111111',
        amountCents: 4488,
    ));

    $invoice = $invoices->findByOrderId(OrderReference::fromString('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa'));

    expect($invoice)->not->toBeNull()
        ->and($invoice->lines())->toHaveCount(2)
        ->and($invoice->lines()[0]->description())->toBe('Nuvora Tee')
        ->and($invoice->lines()[0]->quantity())->toBe(2)
        ->and($invoice->lines()[0]->unitPriceCents())->toBe(1999)
        ->and($invoice->lines()[0]->lineTotalCents())->toBe(3998)
        ->and($invoice->lines()[1]->description())->toBe('Livraison Standard')
        ->and($invoice->lines()[1]->quantity())->toBe(1)
        ->and($invoice->lines()[1]->unitPriceCents())->toBe(490)
        ->and($invoice->amount()->cents())->toBe(4488);
});

it('snapshots the current shop logo on the issued invoice', function () {
    $invoices = new InMemoryInvoiceRepository();
    $logo = InvoiceLogo::of('invoices/logos/shop.png', 'image/png');
    $dispatcher = invoicePaymentDispatcher(
        $invoices,
        new InMemoryInvoiceBrandingRepository(InvoiceBranding::blank()->withLogo($logo)),
    );

    $dispatcher->dispatch(new PaymentCompleted(
        paymentId: 'dddddddd-dddd-4ddd-8ddd-dddddddddddd',
        orderId: 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
        customerId: '11111111-1111-4111-8111-111111111111',
        amountCents: 4488,
    ));

    $invoice = $invoices->findByOrderId(OrderReference::fromString('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa'));

    expect($invoice?->logo()?->relativePath())->toBe('invoices/logos/shop.png')
        ->and($invoice?->logo()?->publicUrl())->toBe('/uploads/invoices/logos/shop.png');
});
