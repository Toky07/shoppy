<?php

declare(strict_types=1);

use App\Product\Application\Command\HideProductReviewCommand;
use App\Product\Application\Command\RestoreProductReviewCommand;
use App\Product\Application\CommandHandler\HideProductReviewCommandHandler;
use App\Product\Application\CommandHandler\RestoreProductReviewCommandHandler;
use App\Product\Application\Query\ListProductReviewsQuery;
use App\Product\Application\QueryHandler\ListProductReviewsQueryHandler;
use App\Product\Application\Port\ReviewAuthors;
use App\Product\Domain\Entity\ProductReview;
use App\Product\Domain\ValueObject\ProductId;
use App\Product\Domain\ValueObject\Rating;
use App\Product\Domain\ValueObject\ReviewAuthorId;
use App\Product\Domain\ValueObject\ReviewBody;
use App\Product\Domain\ValueObject\ReviewId;
use App\Product\Infrastructure\Persistence\InMemoryProductRepository;
use App\Product\Infrastructure\Persistence\InMemoryProductReviewRepository;

it('hides a review from the public list and restores it', function () {
    $products = new InMemoryProductRepository();
    $reviews = new InMemoryProductReviewRepository();
    $productId = '550e8400-e29b-41d4-a716-446655440001';
    $reviewId = 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaa1';
    saveProduct($products, $productId, 'Tee', new DateTimeImmutable('2026-08-19T12:00:00+00:00'));
    $reviews->save(ProductReview::submit(
        ReviewId::fromString($reviewId),
        ProductId::fromString($productId),
        ReviewAuthorId::fromString('11111111-1111-4111-8111-111111111111'),
        Rating::fromInt(1),
        ReviewBody::fromString('Avis insultant.'),
        new DateTimeImmutable('2026-09-22T12:00:00+00:00'),
    ));
    $authors = new class implements ReviewAuthors {
        public function labelsFor(array $authorIds): array
        {
            return array_fill_keys($authorIds, 'ada');
        }
    };
    $list = new ListProductReviewsQueryHandler($products, $reviews, $authors);

    (new HideProductReviewCommandHandler($reviews))->handle(new HideProductReviewCommand($reviewId));

    $hidden = $list->handle(new ListProductReviewsQuery($productId));
    expect($hidden->count)->toBe(0)
        ->and($hidden->items)->toHaveCount(0)
        ->and($hidden->averageRating)->toBeNull()
        ->and($reviews->findById(ReviewId::fromString($reviewId))?->isHidden())->toBeTrue();

    (new RestoreProductReviewCommandHandler($reviews))->handle(new RestoreProductReviewCommand($reviewId));

    $visible = $list->handle(new ListProductReviewsQuery($productId));
    expect($visible->count)->toBe(1)
        ->and($visible->items[0]->body)->toBe('Avis insultant.')
        ->and($reviews->findById(ReviewId::fromString($reviewId))?->isHidden())->toBeFalse();
});
