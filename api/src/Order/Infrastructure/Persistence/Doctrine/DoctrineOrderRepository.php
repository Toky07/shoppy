<?php

declare(strict_types=1);

namespace App\Order\Infrastructure\Persistence\Doctrine;

use App\Order\Domain\Entity\Order;
use App\Order\Domain\Repository\OrderRepository;
use App\Order\Domain\ValueObject\CustomerId;
use App\Order\Domain\ValueObject\OrderId;
use App\Order\Domain\ValueObject\OrderStatus;
use DateTimeImmutable;
use App\Order\Infrastructure\Persistence\Doctrine\Entity\OrderRecord;
use App\User\Infrastructure\Persistence\Doctrine\Entity\UserRecord;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;

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
        $ids = $this->pageIds(
            $this->entityManager->createQueryBuilder()
                ->select('o.id')
                ->from(OrderRecord::class, 'o')
                ->where('o.customerId = :customerId')
                ->setParameter('customerId', $customerId->value()),
            $offset,
            $limit,
        );

        return $this->findByIdsWithItems($ids);
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
        $ids = $this->pageIds(
            $this->entityManager->createQueryBuilder()
                ->select('o.id')
                ->from(OrderRecord::class, 'o'),
            $offset,
            $limit,
        );

        return $this->findByIdsWithItems($ids, withCustomerEmail: true);
    }

    public function countAll(): int
    {
        return (int) $this->entityManager->createQueryBuilder()
            ->select('COUNT(o.id)')
            ->from(OrderRecord::class, 'o')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function paidRevenueCents(?DateTimeImmutable $from = null, ?DateTimeImmutable $until = null): int
    {
        $merchandise = (int) $this->paidPeriodQuery(
            $this->entityManager->createQueryBuilder()
                ->select('COALESCE(SUM(items.quantity * items.unitPriceCents), 0)')
                ->from(OrderRecord::class, 'o')
                ->join('o.items', 'items'),
            $from,
            $until,
        )->getQuery()->getSingleScalarResult();

        $shipping = (int) $this->paidPeriodQuery(
            $this->entityManager->createQueryBuilder()
                ->select('COALESCE(SUM(o.shippingFeeCents), 0)')
                ->from(OrderRecord::class, 'o'),
            $from,
            $until,
        )->getQuery()->getSingleScalarResult();

        return $merchandise + $shipping;
    }

    public function countPlacedBetween(DateTimeImmutable $from, DateTimeImmutable $until): int
    {
        return (int) $this->entityManager->createQueryBuilder()
            ->select('COUNT(o.id)')
            ->from(OrderRecord::class, 'o')
            ->where('o.createdAt >= :from')
            ->andWhere('o.createdAt < :until')
            ->setParameter('from', $from)
            ->setParameter('until', $until)
            ->getQuery()
            ->getSingleScalarResult();
    }

    private function paidPeriodQuery(QueryBuilder $query, ?DateTimeImmutable $from, ?DateTimeImmutable $until): QueryBuilder
    {
        $query
            ->andWhere('o.status = :paid')
            ->setParameter('paid', OrderStatus::paid()->value());

        if ($from !== null) {
            $query->andWhere('o.createdAt >= :from')->setParameter('from', $from);
        }

        if ($until !== null) {
            $query->andWhere('o.createdAt < :until')->setParameter('until', $until);
        }

        return $query;
    }

    /**
     * @return list<string>
     */
    private function pageIds(QueryBuilder $query, int $offset, int $limit): array
    {
        return $query
            ->orderBy('o.createdAt', 'DESC')
            ->addOrderBy('o.id', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getSingleColumnResult();
    }

    /**
     * @param list<string> $ids
     *
     * @return list<Order>
     */
    private function findByIdsWithItems(array $ids, bool $withCustomerEmail = false): array
    {
        if ($ids === []) {
            return [];
        }

        $query = $this->entityManager->createQueryBuilder()
            ->select('o')
            ->addSelect('items')
            ->from(OrderRecord::class, 'o')
            ->leftJoin('o.items', 'items')
            ->where('o.id IN (:ids)')
            ->setParameter('ids', $ids);

        if ($withCustomerEmail) {
            $query
                ->leftJoin(UserRecord::class, 'u', 'ON', 'o.customerId = u.id')
                ->addSelect('u.email AS customerEmail');
        }

        $rows = $query->getQuery()->getResult();
        $indexed = [];

        foreach ($rows as $row) {
            if ($withCustomerEmail) {
                /** @var array{0: OrderRecord, customerEmail: ?string} $row */
                $order = $row[0]->toDomain($row['customerEmail']);
            } else {
                /** @var OrderRecord $row */
                $order = $row->toDomain();
            }

            $indexed[$order->id()->value()] = $order;
        }

        $orders = [];

        foreach ($ids as $id) {
            if (isset($indexed[$id])) {
                $orders[] = $indexed[$id];
            }
        }

        return $orders;
    }
}
