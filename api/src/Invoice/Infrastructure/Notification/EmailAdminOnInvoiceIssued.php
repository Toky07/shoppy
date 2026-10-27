<?php

declare(strict_types=1);

namespace App\Invoice\Infrastructure\Notification;

use App\Email\Domain\Event\EmailRequested;
use App\Email\Domain\ValueObject\EmailAddress;
use App\Invoice\Application\Port\InvoiceAdminContact;
use App\Invoice\Application\Port\InvoiceCustomerContact;
use App\Invoice\Application\Port\InvoicePdfStorage;
use App\Invoice\Domain\Repository\InvoiceRepository;
use App\Invoice\Domain\ValueObject\CustomerReference;
use App\Invoice\Domain\ValueObject\OrderReference;
use App\Shared\Application\Event\InvoiceIssued;
use App\Shared\Domain\Event\EventBus;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final readonly class EmailAdminOnInvoiceIssued implements EventSubscriberInterface
{
    public function __construct(
        private InvoiceRepository $invoices,
        private InvoiceAdminContact $admins,
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
        $adminEmails = $this->admins->emails();

        if ($adminEmails === []) {
            return;
        }

        $invoice = $this->invoices->findByOrderId(OrderReference::fromString($event->orderId));
        $customerEmail = $this->contacts->emailFor(CustomerReference::fromString($event->customerId));
        $attachments = InvoicePdfMailAttachment::list($invoice, $this->pdfs);

        foreach ($adminEmails as $address) {
            $this->events->publish(new EmailRequested(
                EmailAddress::fromString($address),
                $this->mail->adminSubject($event->number),
                $this->mail->adminBody($event, $invoice, $customerEmail),
                $attachments,
            ));
        }
    }
}
