<?php

declare(strict_types=1);

namespace App\Order\Infrastructure\Notification;

use App\Email\Domain\Event\EmailRequested;
use App\Email\Domain\ValueObject\EmailAddress;
use App\Email\Domain\ValueObject\EmailAttachment;
use App\Email\Domain\ValueObject\EmailBody;
use App\Email\Domain\ValueObject\EmailSubject;
use App\Order\Application\Port\CustomerContact;
use App\Order\Domain\Entity\Order;
use App\Order\Domain\Repository\OrderRepository;
use App\Order\Domain\ValueObject\CustomerId;
use App\Order\Domain\ValueObject\OrderId;
use App\Shared\Application\Event\OrderCancelled;
use App\Shared\Application\Event\OrderPlaced;
use App\Shared\Domain\Event\EventBus;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final readonly class EmailCustomerAboutOrder implements EventSubscriberInterface
{
    public function __construct(
        private OrderRepository $orders,
        private CustomerContact $contacts,
        private EventBus $events,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            OrderPlaced::class => 'onOrderPlaced',
            OrderCancelled::class => 'onOrderCancelled',
        ];
    }

    public function onOrderPlaced(OrderPlaced $event): void
    {
        $order = $this->orders->findById(OrderId::fromString($event->orderId));

        if ($order === null) {
            return;
        }

        $this->publish(
            $event->customerId,
            'Commande confirmée',
            sprintf(
                'Votre commande %s est enregistrée. Total : %s.',
                $order->id()->value(),
                self::euro($order->totalCents()),
            ),
            [EmailAttachment::fromContent('recu.txt', 'text/plain', $this->receipt($order))],
        );
    }

    public function onOrderCancelled(OrderCancelled $event): void
    {
        $this->publish(
            $event->customerId,
            'Commande annulée',
            sprintf('Votre commande %s a été annulée.', $event->orderId),
        );
    }

    /**
     * @param list<EmailAttachment> $attachments
     */
    private function publish(string $customerId, string $subject, string $text, array $attachments = []): void
    {
        $address = $this->contacts->emailFor(CustomerId::fromString($customerId));

        if ($address === null) {
            return;
        }

        $this->events->publish(new EmailRequested(
            EmailAddress::fromString($address),
            EmailSubject::fromString($subject),
            EmailBody::fromText($text),
            $attachments,
        ));
    }

    private function receipt(Order $order): string
    {
        $lines = ['Commande '.$order->id()->value()];

        foreach ($order->items() as $item) {
            $lines[] = sprintf(
                '%s x%d — %s',
                $item->name()->value(),
                $item->quantity()->value(),
                self::euro($item->lineTotalCents()),
            );
        }

        $lines[] = 'Total : '.self::euro($order->totalCents());

        return implode("\n", $lines);
    }

    private static function euro(int $cents): string
    {
        return sprintf('%d,%02d €', intdiv($cents, 100), $cents % 100);
    }
}
