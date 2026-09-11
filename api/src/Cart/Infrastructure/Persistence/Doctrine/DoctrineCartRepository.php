<?php

declare(strict_types=1);

namespace App\Cart\Infrastructure\Persistence\Doctrine;

use App\Cart\Domain\Entity\Cart;
use App\Cart\Domain\Repository\CartRepository;
use App\Cart\Domain\ValueObject\CustomerId;
use App\Cart\Infrastructure\Persistence\Doctrine\Entity\CartRecord;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrineCartRepository implements CartRepository
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function save(Cart $cart): void
    {
        $record = $this->findRecordByCustomerId($cart->customerId()->value());

        if ($record === null) {
            $this->entityManager->persist(CartRecord::fromDomain($cart));
            $this->entityManager->flush();

            return;
        }

        $record->updateFromDomain($cart);
        $this->entityManager->flush();
        $record->addItems($cart->items());
        $this->entityManager->flush();
    }

    public function findByCustomerId(CustomerId $customerId): ?Cart
    {
        $record = $this->findRecordByCustomerId($customerId->value());

        return $record?->toDomain();
    }

    private function findRecordByCustomerId(string $customerId): ?CartRecord
    {
        $record = $this->entityManager->createQueryBuilder()
            ->select('c')
            ->from(CartRecord::class, 'c')
            ->where('c.customerId = :customerId')
            ->setParameter('customerId', $customerId)
            ->getQuery()
            ->getOneOrNullResult();

        return $record instanceof CartRecord ? $record : null;
    }
}
