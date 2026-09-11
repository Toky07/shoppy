<?php

declare(strict_types=1);

use App\Product\Domain\Entity\Product;
use App\Product\Domain\Repository\ProductRepository;
use App\Product\Domain\ValueObject\ProductDescription;
use App\Product\Domain\ValueObject\ProductId;
use App\Product\Domain\ValueObject\ProductListCriteria;
use App\Product\Domain\ValueObject\ProductName;
use App\Product\Domain\ValueObject\ProductPrice;
use Doctrine\ORM\EntityManagerInterface;

it('persists and retrieves a product', function () {
    $id = ProductId::fromString('550e8400-e29b-41d4-a716-446655440000');
    $createdAt = new DateTimeImmutable('2026-08-20T12:00:00+00:00');
    $product = Product::create(
        $id,
        ProductName::fromString('Nuvora Tee'),
        ProductPrice::fromCents(1999),
        $createdAt,
        ProductDescription::fromString('Soft cotton t-shirt'),
    );

    $repository = self::getContainer()->get(ProductRepository::class);
    $entityManager = self::getContainer()->get(EntityManagerInterface::class);

    $repository->save($product);
    $entityManager->clear();

    $found = $repository->findById($id);

    expect($found)->not->toBeNull()
        ->and($found->id()->value())->toBe($id->value())
        ->and($found->name()->value())->toBe('Nuvora Tee')
        ->and($found->description()?->value())->toBe('Soft cotton t-shirt')
        ->and($found->price()->cents())->toBe(1999)
        ->and($found->price()->currency())->toBe('EUR')
        ->and($found->stock()->value())->toBe(0)
        ->and($found->image())->toBeNull()
        ->and($found->createdAt()->format(DateTimeInterface::ATOM))->toBe('2026-08-20T12:00:00+00:00');
});

it('returns null when the product does not exist', function () {
    $repository = self::getContainer()->get(ProductRepository::class);

    $found = $repository->findById(
        ProductId::fromString('550e8400-e29b-41d4-a716-446655440000'),
    );

    expect($found)->toBeNull();
});

it('paginates persisted products newest first', function () {
    $repository = self::getContainer()->get(ProductRepository::class);
    $entityManager = self::getContainer()->get(EntityManagerInterface::class);

    $repository->save(Product::create(
        ProductId::fromString('550e8400-e29b-41d4-a716-446655440001'),
        ProductName::fromString('One'),
        ProductPrice::fromCents(100),
        new DateTimeImmutable('2026-08-18T12:00:00+00:00'),
    ));
    $repository->save(Product::create(
        ProductId::fromString('550e8400-e29b-41d4-a716-446655440002'),
        ProductName::fromString('Two'),
        ProductPrice::fromCents(200),
        new DateTimeImmutable('2026-08-19T12:00:00+00:00'),
    ));
    $repository->save(Product::create(
        ProductId::fromString('550e8400-e29b-41d4-a716-446655440003'),
        ProductName::fromString('Three'),
        ProductPrice::fromCents(300),
        new DateTimeImmutable('2026-08-20T12:00:00+00:00'),
    ));
    $entityManager->clear();

    $page = $repository->findPage(0, 2);

    expect($repository->countAll())->toBe(3)
        ->and($page)->toHaveCount(2)
        ->and($page[0]->name()->value())->toBe('Three')
        ->and($page[1]->name()->value())->toBe('Two');
});

it('searches and sorts persisted products', function () {
    $repository = self::getContainer()->get(ProductRepository::class);
    $entityManager = self::getContainer()->get(EntityManagerInterface::class);

    $repository->save(Product::create(
        ProductId::fromString('550e8400-e29b-41d4-a716-446655440001'),
        ProductName::fromString('Nuvora Tee'),
        ProductPrice::fromCents(1999),
        new DateTimeImmutable('2026-08-18T12:00:00+00:00'),
        ProductDescription::fromString('Soft cotton'),
    ));
    $repository->save(Product::create(
        ProductId::fromString('550e8400-e29b-41d4-a716-446655440002'),
        ProductName::fromString('Nuvora Hoodie'),
        ProductPrice::fromCents(4999),
        new DateTimeImmutable('2026-08-19T12:00:00+00:00'),
    ));
    $repository->save(Product::create(
        ProductId::fromString('550e8400-e29b-41d4-a716-446655440003'),
        ProductName::fromString('Ceramic Mug'),
        ProductPrice::fromCents(1299),
        new DateTimeImmutable('2026-08-20T12:00:00+00:00'),
        ProductDescription::fromString('Nuvora logo'),
    ));
    $entityManager->clear();

    $search = ProductListCriteria::fromInput('nuvora', 'price_asc');
    $page = $repository->findPage(0, 20, $search);

    expect($repository->countAll($search))->toBe(3)
        ->and(array_map(static fn ($product) => $product->name()->value(), $page))->toBe([
            'Ceramic Mug',
            'Nuvora Tee',
            'Nuvora Hoodie',
        ]);
});

it('deletes a persisted product', function () {
    $id = ProductId::fromString('550e8400-e29b-41d4-a716-446655440000');
    $repository = self::getContainer()->get(ProductRepository::class);
    $entityManager = self::getContainer()->get(EntityManagerInterface::class);

    $repository->save(Product::create(
        $id,
        ProductName::fromString('Nuvora Tee'),
        ProductPrice::fromCents(1999),
        new DateTimeImmutable('2026-08-20T12:00:00+00:00'),
    ));
    $entityManager->clear();

    $product = $repository->findById($id);
    expect($product)->not->toBeNull();

    $repository->delete($product);
    $entityManager->clear();

    expect($repository->findById($id))->toBeNull();
});
