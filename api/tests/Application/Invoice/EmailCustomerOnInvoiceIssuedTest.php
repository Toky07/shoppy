<?php

declare(strict_types=1);

use App\Email\Domain\Event\EmailRequested;
use App\Invoice\Application\Port\InvoiceCustomerContact;
use App\Invoice\Domain\Entity\Invoice;
use App\Invoice\Domain\ValueObject\CustomerReference;
use App\Invoice\Domain\ValueObject\InvoiceId;
use App\Invoice\Domain\ValueObject\InvoiceLine;
use App\Invoice\Domain\ValueObject\InvoiceNumber;
use App\Invoice\Domain\ValueObject\InvoicePdf;
use App\Invoice\Domain\ValueObject\MoneyAmount;
use App\Invoice\Domain\ValueObject\OrderReference;
use App\Invoice\Domain\ValueObject\PaymentReference;
use App\Invoice\Infrastructure\Notification\EmailCustomerOnInvoiceIssued;
use App\Invoice\Infrastructure\Notification\InvoiceIssuedMail;
use App\Invoice\Infrastructure\Persistence\InMemoryInvoiceRepository;
use App\Shared\Application\Event\InvoiceIssued;
use App\Tests\Doubles\FakeInvoicePdfStorage;
use App\Tests\Doubles\RecordingEventBus;

function emailedInvoice(): Invoice
{
    $invoice = Invoice::issue(
        InvoiceId::fromString('ffffffff-ffff-4fff-8fff-ffffffffffff'),
        InvoiceNumber::fromString('FAC-2026-0001'),
        OrderReference::fromString('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa'),
        CustomerReference::fromString('11111111-1111-4111-8111-111111111111'),
        PaymentReference::fromString('dddddddd-dddd-4ddd-8ddd-dddddddddddd'),
        MoneyAmount::fromCents(4488),
        new DateTimeImmutable('2026-08-20T12:05:00+00:00'),
        [
            InvoiceLine::of('Nuvora Tee', 2, 1999),
            InvoiceLine::of('Livraison Standard', 1, 490),
        ],
    );
    $invoice->attachPdf(InvoicePdf::pathFor($invoice->number()));

    return $invoice;
}

function customerInvoiceMail(): InvoiceIssuedMail
{
    return new InvoiceIssuedMail('http://localhost:5173');
}

it('emails the invoice pdf when an invoice is issued', function () {
    $invoices = new InMemoryInvoiceRepository();
    $invoices->save(emailedInvoice());
    $pdfs = new FakeInvoicePdfStorage();
    $pdfs->store('invoices/pdf/FAC-2026-0001.pdf', "%PDF-1.4\nFAC-2026-0001\n");
    $events = new RecordingEventBus();

    (new EmailCustomerOnInvoiceIssued($invoices, new class implements InvoiceCustomerContact {
        public function emailFor(CustomerReference $customerId): ?string
        {
            return 'ada@shoppy.test';
        }
    }, $pdfs, customerInvoiceMail(), $events))->onInvoiceIssued(new InvoiceIssued(
        invoiceId: 'ffffffff-ffff-4fff-8fff-ffffffffffff',
        number: 'FAC-2026-0001',
        orderId: 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
        customerId: '11111111-1111-4111-8111-111111111111',
        paymentId: 'dddddddd-dddd-4ddd-8ddd-dddddddddddd',
        amountCents: 4488,
    ));

    expect($events->published)->toHaveCount(1)
        ->and($events->published[0])->toBeInstanceOf(EmailRequested::class)
        ->and($events->published[0]->to->value())->toBe('ada@shoppy.test')
        ->and($events->published[0]->subject->value())->toBe('Votre facture FAC-2026-0001')
        ->and($events->published[0]->body->text())->toContain('Votre facture FAC-2026-0001 est prête')
        ->and($events->published[0]->body->text())->toContain('Total : 44,88 €')
        ->and($events->published[0]->body->html())->toContain('Votre facture est prête')
        ->and($events->published[0]->body->html())->toContain('Nuvora Tee')
        ->and($events->published[0]->attachments)->toHaveCount(1)
        ->and($events->published[0]->attachments[0]->filename())->toBe('FAC-2026-0001.pdf')
        ->and($events->published[0]->attachments[0]->mimeType())->toBe('application/pdf')
        ->and($events->published[0]->attachments[0]->content())->toBe("%PDF-1.4\nFAC-2026-0001\n");
});

it('skips the invoice email when the customer has no address', function () {
    $events = new RecordingEventBus();

    (new EmailCustomerOnInvoiceIssued(
        new InMemoryInvoiceRepository(),
        new class implements InvoiceCustomerContact {
            public function emailFor(CustomerReference $customerId): ?string
            {
                return null;
            }
        },
        new FakeInvoicePdfStorage(),
        customerInvoiceMail(),
        $events,
    ))->onInvoiceIssued(new InvoiceIssued(
        invoiceId: 'ffffffff-ffff-4fff-8fff-ffffffffffff',
        number: 'FAC-2026-0001',
        orderId: 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
        customerId: '11111111-1111-4111-8111-111111111111',
        paymentId: 'dddddddd-dddd-4ddd-8ddd-dddddddddddd',
        amountCents: 4488,
    ));

    expect($events->published)->toBe([]);
});
