<?php

declare(strict_types=1);

use App\Product\Application\Port\ReviewAuthors;
use App\Product\Application\Query\ListAdminReviewsQuery;
use App\Product\Application\QueryHandler\ListAdminReviewsQueryHandler;
use App\Product\Domain\Entity\ProductReview;
use App\Product\Domain\Exception\InvalidReviewPagination;
use App\Product\Domain\ValueObject\ProductId;
use App\Product\Domain\ValueObject\Rating;
use App\Product\Domain\ValueObject\ReviewAuthorId;
use App\Product\Domain\ValueObject\ReviewBody;
use App\Product\Domain\ValueObject\ReviewId;
use App\Product\Infrastructure\Persistence\InMemoryProductRepository;
use App\Product\Infrastructure\Persistence\InMemoryProductReviewRepository;

it('lists reported reviews first for moderation', function () {
    $products = new InMemoryProductRepository();
    $reviews = new InMemoryProductReviewRepository();
    $productId = '550e8400-e29b-41d4-a716-446655440001';
    saveProduct($products, $productId, 'Tee', new DateTimeImmutable('2026-08-19T12:00:00+00:00'));
    $older = ProductReview::submit(
        ReviewId::fromString('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaa1'),
        ProductId::fromString($productId),
        ReviewAuthorId::fromString('11111111-1111-4111-8111-111111111111'),
        Rating::fromInt(5),
        ReviewBody::fromString('Nickel.'),
        new DateTimeImmutable('2026-08-18T12:00:00+00:00'),
    );
    $reported = ProductReview::submit(
        ReviewId::fromString('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaa2'),
        ProductId::fromString($productId),
        ReviewAuthorId::fromString('22222222-2222-4222-8222-222222222222'),
        Rating::fromInt(1),
        ReviewBody::fromString('Spam.'),
        new DateTimeImmutable('2026-08-17T12:00:00+00:00'),
    );
    $reported->report();
    $reviews->save($older);
    $reviews->save($reported);

    $authors = new class implements ReviewAuthors {
        public function labelsFor(array $authorIds): array
        {
            return array_fill_keys($authorIds, 'client');
        }
    };
    $handler = new ListAdminReviewsQueryHandler($reviews, $products, $authors);

    $all = $handler->handle(new ListAdminReviewsQuery());
    $flagged = $handler->handle(new ListAdminReviewsQuery(reportedOnly: true));

    expect($all->total)->toBe(2)
        ->and($all->items[0]->id)->toBe('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaa2')
        ->and($all->items[0]->reported)->toBeTrue()
        ->and($all->items[0]->productName)->toBe('Tee')
        ->and($flagged->total)->toBe(1)
        ->and($flagged->items[0]->body)->toBe('Spam.');
});

it('rejects an invalid admin review page', function () {
    $handler = new ListAdminReviewsQueryHandler(
        new InMemoryProductReviewRepository(),
        new InMemoryProductRepository(),
        new class implements ReviewAuthors {
            public function labelsFor(array $authorIds): array
            {
                return [];
            }
        },
    );

    $handler->handle(new ListAdminReviewsQuery(page: 0));
})->throws(InvalidReviewPagination::class);
