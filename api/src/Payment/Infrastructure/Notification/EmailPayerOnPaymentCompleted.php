<?php

declare(strict_types=1);

namespace App\Payment\Infrastructure\Notification;

use App\Email\Domain\Event\EmailRequested;
use App\Email\Domain\ValueObject\EmailAddress;
use App\Email\Domain\ValueObject\EmailBody;
use App\Email\Domain\ValueObject\EmailSubject;
use App\Payment\Application\Port\PayerContact;
use App\Payment\Domain\ValueObject\CustomerReference;
use App\Shared\Application\Event\PaymentCompleted;
use App\Shared\Domain\Event\EventBus;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final readonly class EmailPayerOnPaymentCompleted implements EventSubscriberInterface
{
    public function __construct(
        private PayerContact $contacts,
        private EventBus $events,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [PaymentCompleted::class => 'onPaymentCompleted'];
    }

    public function onPaymentCompleted(PaymentCompleted $event): void
    {
        $address = $this->contacts->emailFor(CustomerReference::fromString($event->customerId));

        if ($address === null) {
            return;
        }

        $this->events->publish(new EmailRequested(
            EmailAddress::fromString($address),
            EmailSubject::fromString('Paiement reçu'),
            EmailBody::fromText(sprintf(
                'Nous avons reçu votre paiement de %s pour la commande %s.',
                self::euro($event->amountCents),
                $event->orderId,
            )),
        ));
    }

    private static function euro(int $cents): string
    {
        return sprintf('%d,%02d €', intdiv($cents, 100), $cents % 100);
    }
}
