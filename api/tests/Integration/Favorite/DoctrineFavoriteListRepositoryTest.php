<?php

declare(strict_types=1);

use App\Favorite\Domain\Entity\FavoriteList;
use App\Favorite\Domain\Repository\FavoriteListRepository;
use App\Favorite\Domain\ValueObject\CustomerId;
use App\Favorite\Domain\ValueObject\FavoriteProductId;
use Doctrine\ORM\EntityManagerInterface;

it('persists a favorite list by customer', function () {
    $customerId = CustomerId::fromString('11111111-1111-4111-8111-111111111111');
    $list = FavoriteList::empty($customerId);
    $list->add(FavoriteProductId::fromString('550e8400-e29b-41d4-a716-446655440000'));
    $list->add(FavoriteProductId::fromString('550e8400-e29b-41d4-a716-446655440001'));

    $repository = self::getContainer()->get(FavoriteListRepository::class);
    $entityManager = self::getContainer()->get(EntityManagerInterface::class);

    $repository->save($list);
    $entityManager->clear();

    $found = $repository->findByCustomerId($customerId);

    expect($found)->not->toBeNull()
        ->and(array_map(
            static fn (FavoriteProductId $id): string => $id->value(),
            $found->productIds(),
        ))->toBe([
            '550e8400-e29b-41d4-a716-446655440001',
            '550e8400-e29b-41d4-a716-446655440000',
        ]);

    $found->remove(FavoriteProductId::fromString('550e8400-e29b-41d4-a716-446655440001'));
    $repository->save($found);
    $entityManager->clear();

    $updated = $repository->findByCustomerId($customerId);

    expect($updated)->not->toBeNull()
        ->and($updated->productIds())->toHaveCount(1)
        ->and($updated->productIds()[0]->value())->toBe('550e8400-e29b-41d4-a716-446655440000');
});
