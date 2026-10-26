<?php

declare(strict_types=1);

namespace App\Cart\Infrastructure\Persistence\Doctrine;

use App\Cart\Domain\Entity\Cart;
use App\Cart\Domain\Exception\CartAlreadyCheckingOut;
use App\Cart\Domain\Exception\InsufficientCartStock;
use App\Cart\Domain\Repository\CartRepository;
use App\Cart\Domain\ValueObject\CartItem;
use App\Cart\Domain\ValueObject\CartProductId;
use App\Cart\Domain\ValueObject\CartVariantId;
use App\Cart\Domain\ValueObject\CustomerId;
use App\Cart\Infrastructure\Persistence\Doctrine\Entity\CartRecord;
use App\Shared\Application\Transaction\TransactionRunner;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrineCartRepository implements CartRepository
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private TransactionRunner $transactions,
    ) {
    }

    public function save(Cart $cart): void
    {
        $this->transactions->run(function () use ($cart): void {
            $record = $this->findRecordByCustomerId($cart->customerId()->value());
            $before = $record === null ? [] : $this->quantities($record->toDomain());
            $after = $this->quantities($cart);
            $this->applyReservation($before, $after);

            if ($record === null) {
                $this->entityManager->persist(CartRecord::fromDomain($cart));
                $this->entityManager->flush();

                return;
            }

            $record->updateFromDomain($cart);
            $this->entityManager->flush();
            $record->addItems($cart->items());
            $this->entityManager->flush();
        });
    }

    public function findByCustomerId(CustomerId $customerId): ?Cart
    {
        $record = $this->findRecordByCustomerId($customerId->value());

        return $record?->toDomain();
    }

    public function claimForCheckout(Cart $cart): void
    {
        $claimed = $this->entityManager->getConnection()->executeStatement(
            'UPDATE carts SET version = version + 1 WHERE id = :id AND version = :version',
            [
                'id' => $cart->id()->value(),
                'version' => $cart->version(),
            ],
        );

        if ((int) $claimed !== 1) {
            throw new CartAlreadyCheckingOut();
        }

        $cart->claimCheckout();
        $record = $this->entityManager->find(CartRecord::class, $cart->id()->value());

        if ($record instanceof CartRecord) {
            $record->syncVersion($cart->version());
        }
    }

    public function reservedQuantity(
        CartProductId $productId,
        ?CartVariantId $variantId,
        CustomerId $exceptCustomerId,
    ): int {
        $parameters = [
            'productId' => $productId->value(),
            'customerId' => $exceptCustomerId->value(),
        ];

        if ($variantId === null) {
            $variantCondition = 'i.variant_id IS NULL';
        } else {
            $variantCondition = 'i.variant_id = :variantId';
            $parameters['variantId'] = $variantId->value();
        }

        $reserved = $this->entityManager->getConnection()->fetchOne(
            "SELECT COALESCE(SUM(i.quantity), 0)
             FROM cart_items i
             INNER JOIN carts c ON c.id = i.cart_id
             WHERE i.product_id = :productId
               AND c.customer_id != :customerId
               AND {$variantCondition}",
            $parameters,
        );

        return (int) $reserved;
    }

    public function reservedQuantities(array $items, CustomerId $exceptCustomerId): array
    {
        $quantities = [];

        foreach ($items as $item) {
            $quantities[$item->lineKey()] = 0;
        }

        if ($items === []) {
            return $quantities;
        }

        $productIds = array_values(array_unique(array_map(
            static fn (CartItem $item): string => $item->productId()->value(),
            $items,
        )));

        $rows = $this->entityManager->getConnection()->fetchAllAssociative(
            'SELECT i.product_id, i.variant_id, COALESCE(SUM(i.quantity), 0) AS reserved
             FROM cart_items i
             INNER JOIN carts c ON c.id = i.cart_id
             WHERE i.product_id IN (:productIds)
               AND c.customer_id != :customerId
             GROUP BY i.product_id, i.variant_id',
            [
                'productIds' => $productIds,
                'customerId' => $exceptCustomerId->value(),
            ],
            [
                'productIds' => ArrayParameterType::STRING,
            ],
        );

        foreach ($rows as $row) {
            $key = $row['product_id']."\0".($row['variant_id'] ?? '');

            if (array_key_exists($key, $quantities)) {
                $quantities[$key] = (int) $row['reserved'];
            }
        }

        return $quantities;
    }

    /**
     * @return array<string, int>
     */
    private function quantities(Cart $cart): array
    {
        $quantities = [];

        foreach ($cart->items() as $item) {
            $quantities[$this->key($item->productId()->value(), $item->variantId()?->value())] = $item->quantity()->value();
        }

        return $quantities;
    }

    /**
     * @param array<string, int> $before
     * @param array<string, int> $after
     */
    private function applyReservation(array $before, array $after): void
    {
        foreach (array_unique([...array_keys($before), ...array_keys($after)]) as $key) {
            $delta = ($after[$key] ?? 0) - ($before[$key] ?? 0);

            if ($delta === 0) {
                continue;
            }

            [$productId, $variantId] = explode("\0", $key, 2);
            $this->adjustReserved($productId, $variantId === '' ? null : $variantId, $delta);
        }
    }

    private function adjustReserved(string $productId, ?string $variantId, int $delta): void
    {
        $table = $variantId === null ? 'products' : 'product_variants';
        $id = $variantId ?? $productId;
        $updated = $this->entityManager->getConnection()->executeStatement(
            "UPDATE {$table}
             SET reserved_stock = reserved_stock + :delta
             WHERE id = :id
               AND reserved_stock + :deltaBound >= 0
               AND stock >= reserved_stock + :deltaLimit",
            [
                'delta' => $delta,
                'deltaBound' => $delta,
                'deltaLimit' => $delta,
                'id' => $id,
            ],
        );

        if ((int) $updated === 1) {
            return;
        }

        $exists = $this->entityManager->getConnection()->fetchOne(
            "SELECT 1 FROM {$table} WHERE id = :id",
            ['id' => $id],
        );

        if ($exists === false) {
            return;
        }

        throw new InsufficientCartStock(0, abs($delta));
    }

    private function key(string $productId, ?string $variantId): string
    {
        return $productId."\0".($variantId ?? '');
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
