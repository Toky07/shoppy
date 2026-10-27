<?php

declare(strict_types=1);

use App\Product\Domain\Entity\ProductReview;
use App\Product\Domain\ValueObject\ProductId;
use App\Product\Domain\ValueObject\Rating;
use App\Product\Domain\ValueObject\ReviewAuthorId;
use App\Product\Domain\ValueObject\ReviewBody;
use App\Product\Domain\ValueObject\ReviewId;

function sampleReview(): ProductReview
{
    return ProductReview::submit(
        ReviewId::fromString('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaa1'),
        ProductId::fromString('550e8400-e29b-41d4-a716-446655440001'),
        ReviewAuthorId::fromString('11111111-1111-4111-8111-111111111111'),
        Rating::fromInt(5),
        ReviewBody::fromString('Très beau tee.'),
        new DateTimeImmutable('2026-09-22T12:00:00+00:00'),
    );
}

it('starts visible and unreported', function () {
    $review = sampleReview();

    expect($review->isHidden())->toBeFalse()
        ->and($review->isReported())->toBeFalse();
});

it('marks a review as reported without hiding it', function () {
    $review = sampleReview();

    $review->report();

    expect($review->isReported())->toBeTrue()
        ->and($review->isHidden())->toBeFalse();
});

it('hides a review and can restore it on the storefront', function () {
    $review = sampleReview();
    $review->report();

    $review->hide();
    expect($review->isHidden())->toBeTrue()
        ->and($review->isReported())->toBeTrue();

    $review->restore();
    expect($review->isHidden())->toBeFalse()
        ->and($review->isReported())->toBeTrue();
});

it('reconstitutes hidden and reported flags from persistence', function () {
    $review = ProductReview::reconstitute(
        ReviewId::fromString('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaa1'),
        ProductId::fromString('550e8400-e29b-41d4-a716-446655440001'),
        ReviewAuthorId::fromString('11111111-1111-4111-8111-111111111111'),
        Rating::fromInt(2),
        ReviewBody::fromString('Mauvais produit.'),
        new DateTimeImmutable('2026-09-22T12:00:00+00:00'),
        true,
        true,
    );

    expect($review->isHidden())->toBeTrue()
        ->and($review->isReported())->toBeTrue();
});
