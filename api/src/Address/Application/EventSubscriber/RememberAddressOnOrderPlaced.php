<?php

declare(strict_types=1);

namespace App\Address\Application\EventSubscriber;

use App\Address\Domain\Entity\AddressBook;
use App\Address\Domain\Exception\AddressBookFull;
use App\Address\Domain\Repository\AddressBookRepository;
use App\Address\Domain\ValueObject\CustomerId;
use App\Order\Domain\Repository\OrderRepository;
use App\Order\Domain\ValueObject\OrderId;
use App\Order\Domain\ValueObject\PostalAddress;
use App\Shared\Application\Event\OrderPlaced;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final readonly class RememberAddressOnOrderPlaced implements EventSubscriberInterface
{
    public function __construct(
        private OrderRepository $orders,
        private AddressBookRepository $books,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [OrderPlaced::class => 'onOrderPlaced'];
    }

    public function onOrderPlaced(OrderPlaced $event): void
    {
        $order = $this->orders->findById(OrderId::fromString($event->orderId));
        $shipping = $order?->shippingAddress();

        if ($order === null || $shipping === null) {
            return;
        }

        $customerId = CustomerId::fromString($event->customerId);
        $book = $this->books->findByCustomerId($customerId) ?? AddressBook::empty($customerId);
        $this->remember($book, $shipping, true);
        $billing = $order->billingAddress();

        if ($billing instanceof PostalAddress && !$billing->equals($shipping)) {
            $this->remember($book, $billing, false);
        }

        if ($book->addresses() !== []) {
            $this->books->save($book);
        }
    }

    private function remember(AddressBook $book, PostalAddress $address, bool $asDefault): void
    {
        try {
            $book->remember($address, $asDefault);
        } catch (AddressBookFull) {
        }
    }
}
