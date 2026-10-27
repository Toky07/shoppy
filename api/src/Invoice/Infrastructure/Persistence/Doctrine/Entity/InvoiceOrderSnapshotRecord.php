<?php

declare(strict_types=1);

namespace App\Invoice\Infrastructure\Persistence\Doctrine\Entity;

use App\Invoice\Domain\Entity\InvoiceOrderSnapshot;
use App\Invoice\Domain\ValueObject\CustomerReference;
use App\Invoice\Domain\ValueObject\InvoiceLine;
use App\Invoice\Domain\ValueObject\OrderReference;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'invoice_order_snapshots')]
class InvoiceOrderSnapshotRecord
{
    #[ORM\Id]
    #[ORM\Column(name: 'order_id', length: 36)]
    private string $orderId;

    #[ORM\Column(name: 'customer_id', length: 36)]
    private string $customerId;

    /** @var Collection<int, InvoiceOrderSnapshotItemRecord> */
    #[ORM\OneToMany(targetEntity: InvoiceOrderSnapshotItemRecord::class, mappedBy: 'snapshot', cascade: ['persist'], orphanRemoval: true)]
    #[ORM\OrderBy(['position' => 'ASC'])]
    private Collection $items;

    public function __construct()
    {
        $this->items = new ArrayCollection();
    }

    public static function fromDomain(InvoiceOrderSnapshot $snapshot): self
    {
        $record = new self();
        $record->apply($snapshot);

        return $record;
    }

    public function replaceFromDomain(InvoiceOrderSnapshot $snapshot): void
    {
        $this->items->clear();
        $this->apply($snapshot);
    }

    public function toDomain(): InvoiceOrderSnapshot
    {
        return new InvoiceOrderSnapshot(
            OrderReference::fromString($this->orderId),
            CustomerReference::fromString($this->customerId),
            array_values($this->items->map(
                static fn (InvoiceOrderSnapshotItemRecord $item): InvoiceLine => $item->toDomain(),
            )->toArray()),
        );
    }

    private function apply(InvoiceOrderSnapshot $snapshot): void
    {
        $this->orderId = $snapshot->orderId()->value();
        $this->customerId = $snapshot->customerId()->value();

        foreach ($snapshot->lines() as $position => $line) {
            $this->items->add(InvoiceOrderSnapshotItemRecord::fromDomain($this, $line, $position));
        }
    }
}
