<?php

declare(strict_types=1);

use App\Email\Application\CommandHandler\SendEmailCommandHandler;
use App\Email\Infrastructure\EventSubscriber\SendEmailOnEmailRequested;
use App\Invoice\Application\EventSubscriber\CaptureInvoiceDetailsOnOrderPlaced;
use App\Invoice\Application\EventSubscriber\CreateInvoiceOnPaymentCompleted;
use App\Invoice\Application\EventSubscriber\GenerateInvoicePdfOnInvoiceIssued;
use App\Invoice\Application\Port\InvoiceAdminContact;
use App\Invoice\Application\Port\InvoiceCustomerContact;
use App\Invoice\Domain\ValueObject\CustomerReference;
use App\Invoice\Infrastructure\Notification\EmailAdminOnInvoiceIssued;
use App\Invoice\Infrastructure\Notification\EmailCustomerOnInvoiceIssued;
use App\Invoice\Infrastructure\Notification\InvoiceIssuedMail;
use App\Invoice\Infrastructure\Persistence\InMemoryInvoiceBrandingRepository;
use App\Invoice\Infrastructure\Persistence\InMemoryInvoiceOrderSnapshotRepository;
use App\Invoice\Infrastructure\Persistence\InMemoryInvoiceRepository;
use App\Payment\Application\Command\ConfirmPaymentCommand;
use App\Payment\Application\Command\StartCheckoutCommand;
use App\Payment\Application\CommandHandler\ConfirmPaymentCommandHandler;
use App\Payment\Application\CommandHandler\StartCheckoutCommandHandler;
use App\Payment\Application\EventSubscriber\CreatePaymentOnOrderPlaced;
use App\Payment\Application\PaymentGatewayRegistry;
use App\Payment\Infrastructure\Gateway\LocalPaymentGateway;
use App\Payment\Infrastructure\Gateway\StripePaymentGateway;
use App\Payment\Infrastructure\Persistence\InMemoryPaymentRepository;
use App\Shared\Application\Event\OrderPlaced;
use App\Shared\Application\Event\OrderPlacedItem;
use App\Shared\Application\Event\PaymentCompleted;
use App\Shared\Infrastructure\Event\SymfonyEventBus;
use App\Tests\Doubles\FakeInvoiceLogoStorage;
use App\Tests\Doubles\FakeInvoicePdfRenderer;
use App\Tests\Doubles\FakeInvoicePdfStorage;
use App\Tests\Doubles\FakePayerContact;
use App\Tests\Doubles\FakeStripeCheckoutClient;
use App\Tests\Doubles\FixedClock;
use App\Tests\Doubles\InMemoryMailer;
use App\Tests\Doubles\PendingPayableOrder;
use Symfony\Component\EventDispatcher\EventDispatcher;

function paymentConfirmedInvoiceMailer(): InMemoryMailer
{
    return new InMemoryMailer();
}

function paymentConfirmedInvoiceDispatcher(InMemoryMailer $mailer, InMemoryInvoiceRepository $invoices): EventDispatcher
{
    $snapshots = new InMemoryInvoiceOrderSnapshotRepository();
    $pdfs = new FakeInvoicePdfStorage();
    $dispatcher = new EventDispatcher();
    $events = new SymfonyEventBus($dispatcher);
    $mail = new InvoiceIssuedMail('http://localhost:5173');
    $contacts = new class implements InvoiceCustomerContact {
        public function emailFor(CustomerReference $customerId): ?string
        {
            return 'ada@shoppy.test';
        }
    };
    $admins = new class implements InvoiceAdminContact {
        public function emails(): array
        {
            return ['admin@shoppy.test'];
        }
    };

    $dispatcher->addSubscriber(new CaptureInvoiceDetailsOnOrderPlaced($snapshots));
    $dispatcher->addSubscriber(new CreateInvoiceOnPaymentCompleted(
        $invoices,
        new FixedClock(new DateTimeImmutable('2026-08-20T12:05:00+00:00')),
        $dispatcher,
        $snapshots,
        new InMemoryInvoiceBrandingRepository(),
    ));
    $dispatcher->addSubscriber(new GenerateInvoicePdfOnInvoiceIssued(
        $invoices,
        new FakeInvoicePdfRenderer(),
        $pdfs,
        new FakeInvoiceLogoStorage(),
    ));
    $dispatcher->addSubscriber(new EmailCustomerOnInvoiceIssued($invoices, $contacts, $pdfs, $mail, $events));
    $dispatcher->addSubscriber(new EmailAdminOnInvoiceIssued($invoices, $admins, $contacts, $pdfs, $mail, $events));
    $dispatcher->addSubscriber(new SendEmailOnEmailRequested(new SendEmailCommandHandler($mailer)));

    return $dispatcher;
}

it('sends styled invoice emails when a payment is completed', function () {
    $mailer = paymentConfirmedInvoiceMailer();
    $invoices = new InMemoryInvoiceRepository();
    $dispatcher = paymentConfirmedInvoiceDispatcher($mailer, $invoices);

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

    expect($mailer->sent)->toHaveCount(2)
        ->and($mailer->sent[0]->to()->value())->toBe('ada@shoppy.test')
        ->and($mailer->sent[0]->subject()->value())->toBe('Votre facture FAC-2026-0001')
        ->and($mailer->sent[0]->body()->html())->toContain('Votre facture est prête')
        ->and($mailer->sent[0]->attachments())->toHaveCount(1)
        ->and($mailer->sent[0]->attachments()[0]->filename())->toBe('FAC-2026-0001.pdf')
        ->and($mailer->sent[1]->to()->value())->toBe('admin@shoppy.test')
        ->and($mailer->sent[1]->subject()->value())->toBe('Nouvelle facture FAC-2026-0001')
        ->and($mailer->sent[1]->body()->html())->toContain('Une facture a été émise');
});

it('sends styled invoice emails when a stripe payment is confirmed', function () {
    $mailer = paymentConfirmedInvoiceMailer();
    $invoices = new InMemoryInvoiceRepository();
    $dispatcher = paymentConfirmedInvoiceDispatcher($mailer, $invoices);
    $payments = new InMemoryPaymentRepository();
    $stripe = new FakeStripeCheckoutClient();
    $now = new DateTimeImmutable('2026-08-20T12:00:00+00:00');
    $dispatcher->addSubscriber(new CreatePaymentOnOrderPlaced($payments, new FixedClock($now)));
    $dispatcher->dispatch(new OrderPlaced(
        orderId: 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
        customerId: '11111111-1111-4111-8111-111111111111',
        amountCents: 4488,
        items: [new OrderPlacedItem(description: 'Nuvora Tee', quantity: 2, unitPriceCents: 1999)],
        shippingLabel: 'Standard',
        shippingCents: 490,
    ));

    $gateways = new PaymentGatewayRegistry([
        new LocalPaymentGateway(),
        new StripePaymentGateway($stripe, 'eur', new FakePayerContact()),
    ], 'stripe');

    (new StartCheckoutCommandHandler($payments, $gateways, new PendingPayableOrder()))->handle(new StartCheckoutCommand(
        orderId: 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
        customerId: '11111111-1111-4111-8111-111111111111',
        provider: 'stripe',
        successUrl: 'http://localhost:5173/orders/aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa?payment=success',
        cancelUrl: 'http://localhost:5173/orders/aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa?payment=cancel',
    ));

    $mailer->clear();

    (new ConfirmPaymentCommandHandler(
        $payments,
        $gateways,
        new FixedClock(new DateTimeImmutable('2026-08-20T12:05:00+00:00')),
        $dispatcher,
        new PendingPayableOrder(),
    ))->handle(new ConfirmPaymentCommand(
        orderId: 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
        customerId: '11111111-1111-4111-8111-111111111111',
    ));

    expect($mailer->sent)->toHaveCount(2)
        ->and($mailer->sent[0]->to()->value())->toBe('ada@shoppy.test')
        ->and($mailer->sent[0]->subject()->value())->toStartWith('Votre facture FAC-')
        ->and($mailer->sent[0]->attachments()[0]->mimeType())->toBe('application/pdf')
        ->and($mailer->sent[1]->to()->value())->toBe('admin@shoppy.test');
});
