<?php

declare(strict_types=1);

use App\Product\Application\Port\ReviewAuthors;
use App\Product\Application\Query\ListProductReviewsQuery;
use App\Product\Application\QueryHandler\ListProductReviewsQueryHandler;
use App\Product\Domain\Entity\ProductReview;
use App\Product\Domain\Exception\InvalidReviewPagination;
use App\Product\Domain\ValueObject\ProductId;
use App\Product\Domain\ValueObject\Rating;
use App\Product\Domain\ValueObject\ReviewAuthorId;
use App\Product\Domain\ValueObject\ReviewBody;
use App\Product\Domain\ValueObject\ReviewId;
use App\Product\Infrastructure\Persistence\InMemoryProductRepository;
use App\Product\Infrastructure\Persistence\InMemoryProductReviewRepository;

function listProductReviewsHandler(
    InMemoryProductRepository $products,
    InMemoryProductReviewRepository $reviews,
): ListProductReviewsQueryHandler {
    $authors = new class implements ReviewAuthors {
        public function labelsFor(array $authorIds): array
        {
            return array_fill_keys($authorIds, 'ada');
        }
    };

    return new ListProductReviewsQueryHandler($products, $reviews, $authors);
}

function saveReview(
    InMemoryProductReviewRepository $reviews,
    string $id,
    string $productId,
    string $authorId,
    int $rating,
    DateTimeImmutable $createdAt,
): void {
    $reviews->save(ProductReview::submit(
        ReviewId::fromString($id),
        ProductId::fromString($productId),
        ReviewAuthorId::fromString($authorId),
        Rating::fromInt($rating),
        ReviewBody::fromString('Avis '.$id),
        $createdAt,
    ));
}

it('paginates product reviews newest first and keeps the overall average', function () {
    $products = new InMemoryProductRepository();
    $reviews = new InMemoryProductReviewRepository();
    $productId = '550e8400-e29b-41d4-a716-446655440001';
    saveProduct($products, $productId, 'Tee', new DateTimeImmutable('2026-08-19T12:00:00+00:00'));
    saveReview($reviews, 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaa1', $productId, '11111111-1111-4111-8111-111111111111', 5, new DateTimeImmutable('2026-08-18T12:00:00+00:00'));
    saveReview($reviews, 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaa2', $productId, '22222222-2222-4222-8222-222222222222', 1, new DateTimeImmutable('2026-08-19T12:00:00+00:00'));
    saveReview($reviews, 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaa3', $productId, '33333333-3333-4333-8333-333333333333', 3, new DateTimeImmutable('2026-08-20T12:00:00+00:00'));

    $response = listProductReviewsHandler($products, $reviews)->handle(new ListProductReviewsQuery(
        productId: $productId,
        page: 2,
        limit: 2,
    ));

    expect($response->toArray())->toMatchArray([
        'page' => 2,
        'limit' => 2,
        'count' => 3,
        'averageRating' => 3.0,
    ])
        ->and($response->items)->toHaveCount(1)
        ->and($response->items[0]->id)->toBe('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaa1');
});

it('rejects an invalid review page', function () {
    $products = new InMemoryProductRepository();
    saveProduct($products, '550e8400-e29b-41d4-a716-446655440001', 'Tee', new DateTimeImmutable('2026-08-19T12:00:00+00:00'));

    listProductReviewsHandler($products, new InMemoryProductReviewRepository())->handle(new ListProductReviewsQuery(
        productId: '550e8400-e29b-41d4-a716-446655440001',
        page: 0,
    ));
})->throws(InvalidReviewPagination::class);
