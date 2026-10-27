<?php

declare(strict_types=1);

namespace App\Invoice\Infrastructure\Notification;

use App\Email\Domain\Event\EmailRequested;
use App\Email\Domain\ValueObject\EmailAddress;
use App\Email\Domain\ValueObject\EmailAttachment;
use App\Email\Domain\ValueObject\EmailBody;
use App\Email\Domain\ValueObject\EmailSubject;
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
        $attachments = [];
        $pdfPath = $invoice?->pdf()?->relativePath();
        $pdfContent = $pdfPath === null ? null : $this->pdfs->read($pdfPath);

        if ($invoice !== null && $pdfContent !== null) {
            $attachments[] = EmailAttachment::fromContent(
                $invoice->pdf()?->filename() ?? 'facture.pdf',
                'application/pdf',
                $pdfContent,
            );
        }

        $this->events->publish(new EmailRequested(
            EmailAddress::fromString($address),
            EmailSubject::fromString('Votre facture '.$event->number),
            EmailBody::fromText(sprintf(
                'Votre facture %s est disponible en pièce jointe. Total : %s.',
                $event->number,
                self::euro($event->amountCents),
            )),
            $attachments,
        ));
    }

    private static function euro(int $cents): string
    {
        return sprintf('%d,%02d €', intdiv($cents, 100), $cents % 100);
    }
}
