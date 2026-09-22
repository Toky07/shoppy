<?php

declare(strict_types=1);

use App\Email\Domain\Event\EmailRequested;
use App\Payment\Application\Port\PayerContact;
use App\Payment\Domain\ValueObject\CustomerReference;
use App\Payment\Infrastructure\Notification\EmailPayerOnPaymentCompleted;
use App\Shared\Application\Event\PaymentCompleted;
use App\Tests\Doubles\RecordingEventBus;

it('sends a payment receipt email without an attachment', function () {
    $events = new RecordingEventBus();
    $notifier = new EmailPayerOnPaymentCompleted(new class implements PayerContact {
        public function emailFor(CustomerReference $customerId): ?string
        {
            return 'ada@shoppy.test';
        }
    }, $events);

    $notifier->onPaymentCompleted(new PaymentCompleted(
        paymentId: 'bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb',
        orderId: 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
        customerId: '11111111-1111-4111-8111-111111111111',
        amountCents: 3998,
    ));

    expect($events->published)->toHaveCount(1)
        ->and($events->published[0])->toBeInstanceOf(EmailRequested::class)
        ->and($events->published[0]->to->value())->toBe('ada@shoppy.test')
        ->and($events->published[0]->subject->value())->toBe('Paiement reçu')
        ->and($events->published[0]->body->text())->toBe('Nous avons reçu votre paiement de 39,98 € pour la commande aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa.')
        ->and($events->published[0]->attachments)->toBe([]);
});

it('skips the payment email when the payer has no address', function () {
    $events = new RecordingEventBus();
    $notifier = new EmailPayerOnPaymentCompleted(new class implements PayerContact {
        public function emailFor(CustomerReference $customerId): ?string
        {
            return null;
        }
    }, $events);

    $notifier->onPaymentCompleted(new PaymentCompleted(
        paymentId: 'bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb',
        orderId: 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
        customerId: '11111111-1111-4111-8111-111111111111',
        amountCents: 3998,
    ));

    expect($events->published)->toBe([]);
});
