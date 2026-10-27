<?php

declare(strict_types=1);

namespace App\Invoice\Infrastructure\Notification;

use App\Email\Domain\ValueObject\EmailAttachment;
use App\Invoice\Application\Port\InvoicePdfStorage;
use App\Invoice\Domain\Entity\Invoice;

final readonly class InvoicePdfMailAttachment
{
    /**
     * @return list<EmailAttachment>
     */
    public static function list(?Invoice $invoice, InvoicePdfStorage $pdfs): array
    {
        $pdfPath = $invoice?->pdf()?->relativePath();
        $pdfContent = $pdfPath === null ? null : $pdfs->read($pdfPath);

        if ($invoice === null || $pdfContent === null) {
            return [];
        }

        return [
            EmailAttachment::fromContent(
                $invoice->pdf()?->filename() ?? 'facture.pdf',
                'application/pdf',
                $pdfContent,
            ),
        ];
    }
}
