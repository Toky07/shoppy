<?php

declare(strict_types=1);

namespace App\Order\Infrastructure\Persistence\Doctrine;

use App\Order\Domain\Entity\Order;
use App\Order\Domain\Repository\OrderRepository;
use App\Order\Domain\ValueObject\CustomerId;
use App\Order\Domain\ValueObject\OrderId;
use App\Order\Infrastructure\Persistence\Doctrine\Entity\OrderRecord;
use Doctrine\ORM\EntityManagerInterface;
use App\User\Infrastructure\Persistence\Doctrine\Entity\UserRecord;

final readonly class DoctrineOrderRepository implements OrderRepository
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function save(Order $order): void
    {
        $record = $this->entityManager->find(OrderRecord::class, $order->id()->value());

        if ($record === null) {
            $this->entityManager->persist(OrderRecord::fromDomain($order));
        } else {
            $record->updateFromDomain($order);
        }

        $this->entityManager->flush();
    }

    public function findById(OrderId $id): ?Order
    {
        $record = $this->entityManager->find(OrderRecord::class, $id->value());

        return $record?->toDomain();
    }

    public function findPageByCustomer(CustomerId $customerId, int $offset, int $limit): array
    {
        $records = $this->entityManager->createQueryBuilder()
            ->select('o')
            ->from(OrderRecord::class, 'o')
            ->where('o.customerId = :customerId')
            ->setParameter('customerId', $customerId->value())
            ->orderBy('o.createdAt', 'DESC')
            ->addOrderBy('o.id', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        return array_map(
            static fn (OrderRecord $record): Order => $record->toDomain(),
            $records,
        );
    }

    public function countByCustomer(CustomerId $customerId): int
    {
        return (int) $this->entityManager->createQueryBuilder()
            ->select('COUNT(o.id)')
            ->from(OrderRecord::class, 'o')
            ->where('o.customerId = :customerId')
            ->setParameter('customerId', $customerId->value())
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findPage(int $offset, int $limit): array
    {
        $records = $this->entityManager->createQueryBuilder()
            ->select('o')
            ->from(OrderRecord::class, 'o')
            ->join(
                UserRecord::class,
                'u',
                'ON',
                'o.customerId = u.id'
            )
            ->addSelect('u.email as customerEmail')
            ->orderBy('o.createdAt', 'DESC')
            ->addOrderBy('o.id', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        return array_map(
            static fn (array $record): Order => $record[0]->toDomain($record['customerEmail']),
            $records,
        );
    }

    public function countAll(): int
    {
        return (int) $this->entityManager->createQueryBuilder()
            ->select('COUNT(o.id)')
            ->from(OrderRecord::class, 'o')
            ->getQuery()
            ->getSingleScalarResult();
    }
}
