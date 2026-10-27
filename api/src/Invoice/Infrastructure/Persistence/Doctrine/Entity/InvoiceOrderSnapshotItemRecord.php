<?php

declare(strict_types=1);

namespace App\Invoice\Infrastructure\Persistence\Doctrine\Entity;

use App\Invoice\Domain\ValueObject\InvoiceLine;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'invoice_order_snapshot_items')]
class InvoiceOrderSnapshotItemRecord
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: InvoiceOrderSnapshotRecord::class, inversedBy: 'items')]
    #[ORM\JoinColumn(name: 'order_id', referencedColumnName: 'order_id', nullable: false, onDelete: 'CASCADE')]
    private InvoiceOrderSnapshotRecord $snapshot;

    #[ORM\Column(length: 255)]
    private string $description;

    #[ORM\Column]
    private int $quantity;

    #[ORM\Column(name: 'unit_price_cents')]
    private int $unitPriceCents;

    #[ORM\Column]
    private int $position;

    public static function fromDomain(InvoiceOrderSnapshotRecord $snapshot, InvoiceLine $line, int $position): self
    {
        $record = new self();
        $record->snapshot = $snapshot;
        $record->description = $line->description();
        $record->quantity = $line->quantity();
        $record->unitPriceCents = $line->unitPriceCents();
        $record->position = $position;

        return $record;
    }

    public function toDomain(): InvoiceLine
    {
        return InvoiceLine::of($this->description, $this->quantity, $this->unitPriceCents);
    }
}
