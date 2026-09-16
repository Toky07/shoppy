<?php

declare(strict_types=1);

namespace App\Payment\Infrastructure\Persistence\Doctrine;

use App\Payment\Domain\Entity\Payment;
use App\Payment\Domain\Repository\PaymentRepository;
use App\Payment\Domain\ValueObject\OrderReference;
use App\Payment\Infrastructure\Persistence\Doctrine\Entity\PaymentRecord;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrinePaymentRepository implements PaymentRepository
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function save(Payment $payment): void
    {
        $record = $this->findRecordByOrderId($payment->orderId()->value());

        if ($record === null) {
            $this->entityManager->persist(PaymentRecord::fromDomain($payment));
        } else {
            $record->updateFromDomain($payment);
        }

        $this->entityManager->flush();
    }

    public function findByOrderId(OrderReference $orderId): ?Payment
    {
        return $this->findRecordByOrderId($orderId->value())?->toDomain();
    }

    public function findByProviderReference(string $providerReference): ?Payment
    {
        $record = $this->entityManager->createQueryBuilder()
            ->select('p')
            ->from(PaymentRecord::class, 'p')
            ->where('p.providerReference = :providerReference')
            ->setParameter('providerReference', $providerReference)
            ->getQuery()
            ->getOneOrNullResult();

        return $record instanceof PaymentRecord ? $record->toDomain() : null;
    }

    private function findRecordByOrderId(string $orderId): ?PaymentRecord
    {
        $record = $this->entityManager->createQueryBuilder()
            ->select('p')
            ->from(PaymentRecord::class, 'p')
            ->where('p.orderId = :orderId')
            ->setParameter('orderId', $orderId)
            ->getQuery()
            ->getOneOrNullResult();

        return $record instanceof PaymentRecord ? $record : null;
    }
}
