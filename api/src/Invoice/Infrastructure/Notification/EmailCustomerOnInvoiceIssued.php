<?php

declare(strict_types=1);

namespace App\Invoice\Infrastructure\Notification;

use App\Email\Domain\Event\EmailRequested;
use App\Email\Domain\ValueObject\EmailAddress;
use App\Invoice\Application\Port\InvoiceCustomerContact;
use App\Invoice\Application\Port\InvoicePdfStorage;
use App\Invoice\Domain\Repository\InvoiceRepository;
use App\Invoice\Domain\ValueObject\CustomerReference;
use App\Invoice\Domain\ValueObject\OrderReference;
use App\Shared\Application\Event\InvoiceIssued;
use App\Shared\Domain\Event\EventBus;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final readonly class EmailCustomerOnInvoiceIssued implements EventSubscriberInterface
{
    public function __construct(
        private InvoiceRepository $invoices,
        private InvoiceCustomerContact $contacts,
        private InvoicePdfStorage $pdfs,
        private InvoiceIssuedMail $mail,
        private EventBus $events,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [InvoiceIssued::class => 'onInvoiceIssued'];
    }

    public function onInvoiceIssued(InvoiceIssued $event): void
    {
        $address = $this->contacts->emailFor(CustomerReference::fromString($event->customerId));

        if ($address === null) {
            return;
        }

        $invoice = $this->invoices->findByOrderId(OrderReference::fromString($event->orderId));

        $this->events->publish(new EmailRequested(
            EmailAddress::fromString($address),
            $this->mail->customerSubject($event->number),
            $this->mail->customerBody($event, $invoice),
            InvoicePdfMailAttachment::list($invoice, $this->pdfs),
        ));
    }
}
