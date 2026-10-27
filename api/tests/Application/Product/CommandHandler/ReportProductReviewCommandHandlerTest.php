<?php

declare(strict_types=1);

use App\Product\Application\Command\ReportProductReviewCommand;
use App\Product\Application\CommandHandler\ReportProductReviewCommandHandler;
use App\Product\Domain\Entity\ProductReview;
use App\Product\Domain\Exception\CannotReportOwnReview;
use App\Product\Domain\Exception\ReviewNotFound;
use App\Product\Domain\ValueObject\ProductId;
use App\Product\Domain\ValueObject\Rating;
use App\Product\Domain\ValueObject\ReviewAuthorId;
use App\Product\Domain\ValueObject\ReviewBody;
use App\Product\Domain\ValueObject\ReviewId;
use App\Product\Infrastructure\Persistence\InMemoryProductRepository;
use App\Product\Infrastructure\Persistence\InMemoryProductReviewRepository;

function reportReviewSetup(): array
{
    $products = new InMemoryProductRepository();
    $reviews = new InMemoryProductReviewRepository();
    $productId = '550e8400-e29b-41d4-a716-446655440001';
    $authorId = '11111111-1111-4111-8111-111111111111';
    $reviewId = 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaa1';
    saveProduct($products, $productId, 'Tee', new DateTimeImmutable('2026-08-19T12:00:00+00:00'));
    $reviews->save(ProductReview::submit(
        ReviewId::fromString($reviewId),
        ProductId::fromString($productId),
        ReviewAuthorId::fromString($authorId),
        Rating::fromInt(5),
        ReviewBody::fromString('Très beau tee.'),
        new DateTimeImmutable('2026-09-22T12:00:00+00:00'),
    ));

    return [
        'handler' => new ReportProductReviewCommandHandler($products, $reviews),
        'reviews' => $reviews,
        'productId' => $productId,
        'authorId' => $authorId,
        'reviewId' => $reviewId,
    ];
}

it('lets another customer report a review', function () {
    $setup = reportReviewSetup();

    $setup['handler']->handle(new ReportProductReviewCommand(
        $setup['productId'],
        $setup['reviewId'],
        '22222222-2222-4222-8222-222222222222',
    ));

    $stored = $setup['reviews']->findById(ReviewId::fromString($setup['reviewId']));

    expect($stored?->isReported())->toBeTrue()
        ->and($stored?->isHidden())->toBeFalse();
});

it('refuses reporting your own review', function () {
    $setup = reportReviewSetup();

    $setup['handler']->handle(new ReportProductReviewCommand(
        $setup['productId'],
        $setup['reviewId'],
        $setup['authorId'],
    ));
})->throws(CannotReportOwnReview::class);

it('refuses a report on a missing review', function () {
    $setup = reportReviewSetup();

    $setup['handler']->handle(new ReportProductReviewCommand(
        $setup['productId'],
        'bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbb1',
        '22222222-2222-4222-8222-222222222222',
    ));
})->throws(ReviewNotFound::class);
