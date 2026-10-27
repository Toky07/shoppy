<?php

declare(strict_types=1);

namespace App\Invoice\Infrastructure\Persistence\Doctrine\Entity;

use App\Invoice\Domain\Entity\Invoice;
use App\Invoice\Domain\ValueObject\CustomerReference;
use App\Invoice\Domain\ValueObject\InvoiceId;
use App\Invoice\Domain\ValueObject\InvoiceLine;
use App\Invoice\Domain\ValueObject\InvoiceLogo;
use App\Invoice\Domain\ValueObject\InvoiceNumber;
use App\Invoice\Domain\ValueObject\MoneyAmount;
use App\Invoice\Domain\ValueObject\OrderReference;
use App\Invoice\Domain\ValueObject\PaymentReference;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'invoices')]
#[ORM\UniqueConstraint(name: 'uniq_invoices_number', columns: ['number'])]
#[ORM\UniqueConstraint(name: 'uniq_invoices_order_id', columns: ['order_id'])]
#[ORM\Index(name: 'idx_invoices_year_sequence', columns: ['year', 'sequence'])]
class InvoiceRecord
{
    #[ORM\Id]
    #[ORM\Column(length: 36)]
    private string $id;

    #[ORM\Column(length: 32)]
    private string $number;

    #[ORM\Column]
    private int $year;

    #[ORM\Column]
    private int $sequence;

    #[ORM\Column(name: 'order_id', length: 36)]
    private string $orderId;

    #[ORM\Column(name: 'customer_id', length: 36)]
    private string $customerId;

    #[ORM\Column(name: 'payment_id', length: 36)]
    private string $paymentId;

    #[ORM\Column(name: 'amount_cents')]
    private int $amountCents;

    #[ORM\Column(name: 'issued_at')]
    private DateTimeImmutable $issuedAt;

    #[ORM\Column(name: 'logo_relative_path', length: 255, nullable: true)]
    private ?string $logoRelativePath = null;

    #[ORM\Column(name: 'logo_mime_type', length: 32, nullable: true)]
    private ?string $logoMimeType = null;

    /** @var Collection<int, InvoiceItemRecord> */
    #[ORM\OneToMany(targetEntity: InvoiceItemRecord::class, mappedBy: 'invoice', cascade: ['persist'], orphanRemoval: true)]
    #[ORM\OrderBy(['position' => 'ASC'])]
    private Collection $items;

    public function __construct()
    {
        $this->items = new ArrayCollection();
    }

    public static function fromDomain(Invoice $invoice): self
    {
        $record = new self();
        $record->id = $invoice->id()->value();
        $record->number = $invoice->number()->value();
        $record->year = $invoice->number()->year();
        $record->sequence = $invoice->number()->sequence();
        $record->orderId = $invoice->orderId()->value();
        $record->customerId = $invoice->customerId()->value();
        $record->paymentId = $invoice->paymentId()->value();
        $record->amountCents = $invoice->amount()->cents();
        $record->issuedAt = $invoice->issuedAt();
        $record->logoRelativePath = $invoice->logo()?->relativePath();
        $record->logoMimeType = $invoice->logo()?->mimeType();

        foreach ($invoice->lines() as $position => $line) {
            $record->items->add(InvoiceItemRecord::fromDomain($record, $line, $position));
        }

        return $record;
    }

    public function toDomain(): Invoice
    {
        return Invoice::issue(
            InvoiceId::fromString($this->id),
            InvoiceNumber::fromString($this->number),
            OrderReference::fromString($this->orderId),
            CustomerReference::fromString($this->customerId),
            PaymentReference::fromString($this->paymentId),
            MoneyAmount::fromCents($this->amountCents),
            $this->issuedAt,
            array_values($this->items->map(
                static fn (InvoiceItemRecord $item): InvoiceLine => $item->toDomain(),
            )->toArray()),
            $this->logoRelativePath !== null && $this->logoMimeType !== null
                ? InvoiceLogo::of($this->logoRelativePath, $this->logoMimeType)
                : null,
        );
    }
}
