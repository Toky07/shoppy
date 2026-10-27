<?php

declare(strict_types=1);

use App\Invoice\Application\EventSubscriber\CreateInvoiceOnPaymentCompleted;
use App\Invoice\Domain\ValueObject\OrderReference;
use App\Invoice\Infrastructure\Persistence\InMemoryInvoiceRepository;
use App\Shared\Application\Event\InvoiceIssued;
use App\Shared\Application\Event\PaymentCompleted;
use App\Tests\Doubles\FixedClock;
use Symfony\Component\EventDispatcher\EventDispatcher;

function invoicePaymentDispatcher(InMemoryInvoiceRepository $invoices = new InMemoryInvoiceRepository()): EventDispatcher
{
    $dispatcher = new EventDispatcher();
    $dispatcher->addSubscriber(new CreateInvoiceOnPaymentCompleted(
        $invoices,
        new FixedClock(new DateTimeImmutable('2026-08-20T12:05:00+00:00')),
        $dispatcher,
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
