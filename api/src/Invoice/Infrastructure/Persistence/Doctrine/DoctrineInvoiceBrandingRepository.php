<?php

declare(strict_types=1);

namespace App\Invoice\Infrastructure\Persistence\Doctrine;

use App\Invoice\Domain\Entity\InvoiceBranding;
use App\Invoice\Domain\Repository\InvoiceBrandingRepository;
use App\Invoice\Infrastructure\Persistence\Doctrine\Entity\InvoiceBrandingRecord;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrineInvoiceBrandingRepository implements InvoiceBrandingRepository
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function get(): InvoiceBranding
    {
        return $this->findRecord()?->toDomain() ?? InvoiceBranding::blank();
    }

    public function save(InvoiceBranding $branding): void
    {
        $record = $this->findRecord();

        if ($record === null) {
            $this->entityManager->persist(InvoiceBrandingRecord::fromDomain($branding));
        } else {
            $record->replaceFromDomain($branding);
        }

        $this->entityManager->flush();
    }

    private function findRecord(): ?InvoiceBrandingRecord
    {
        $record = $this->entityManager->find(InvoiceBrandingRecord::class, InvoiceBrandingRecord::DEFAULT_ID);

        return $record instanceof InvoiceBrandingRecord ? $record : null;
    }
}
