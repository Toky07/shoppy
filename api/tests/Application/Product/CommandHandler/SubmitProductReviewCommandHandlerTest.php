<?php

declare(strict_types=1);

use App\Product\Application\Command\SubmitProductReviewCommand;
use App\Product\Application\CommandHandler\SubmitProductReviewCommandHandler;
use App\Product\Application\Port\ReviewAuthors;
use App\Product\Application\Query\ListProductReviewsQuery;
use App\Product\Application\QueryHandler\ListProductReviewsQueryHandler;
use App\Product\Domain\Exception\ProductNotFound;
use App\Product\Domain\ValueObject\ProductId;
use App\Product\Infrastructure\Persistence\InMemoryProductRepository;
use App\Product\Infrastructure\Persistence\InMemoryProductReviewRepository;
use App\Tests\Doubles\FixedClock;

it('publishes one review per customer and lets them revise it', function () {
    $products = new InMemoryProductRepository();
    $reviews = new InMemoryProductReviewRepository();
    saveProduct($products, '550e8400-e29b-41d4-a716-446655440001', 'Tee', new DateTimeImmutable('2026-08-19T12:00:00+00:00'));
    $clock = new FixedClock(new DateTimeImmutable('2026-09-22T12:00:00+00:00'));
    $submit = new SubmitProductReviewCommandHandler($products, $reviews, $clock);
    $authorId = '660e8400-e29b-41d4-a716-446655440010';

    $submit->handle(new SubmitProductReviewCommand('550e8400-e29b-41d4-a716-446655440001', $authorId, 5, 'Très beau tee.'));
    $submit->handle(new SubmitProductReviewCommand('550e8400-e29b-41d4-a716-446655440001', $authorId, 4, 'Un peu grand.'));

    $authors = new class implements ReviewAuthors {
        public function labelsFor(array $authorIds): array
        {
            return array_fill_keys($authorIds, 'ada');
        }
    };
    $list = (new ListProductReviewsQueryHandler($products, $reviews, $authors))->handle(
        new ListProductReviewsQuery('550e8400-e29b-41d4-a716-446655440001', $authorId),
    );

    expect($reviews->findByProduct(ProductId::fromString('550e8400-e29b-41d4-a716-446655440001')))->toHaveCount(1)
        ->and($list->count)->toBe(1)
        ->and($list->averageRating)->toBe(4.0)
        ->and($list->items[0]->body)->toBe('Un peu grand.')
        ->and($list->items[0]->author)->toBe('ada')
        ->and($list->items[0]->mine)->toBeTrue();
});

it('refuses a review on a draft', function () {
    $products = new InMemoryProductRepository();
    saveProduct($products, '550e8400-e29b-41d4-a716-446655440001', 'Draft Tee', new DateTimeImmutable('2026-08-19T12:00:00+00:00'));
    $draft = $products->findById(ProductId::fromString('550e8400-e29b-41d4-a716-446655440001'));
    $draft->changePublication(false);
    $products->save($draft);

    $submit = new SubmitProductReviewCommandHandler(
        $products,
        new InMemoryProductReviewRepository(),
        new FixedClock(new DateTimeImmutable('2026-09-22T12:00:00+00:00')),
    );

    expect(fn () => $submit->handle(new SubmitProductReviewCommand(
        '550e8400-e29b-41d4-a716-446655440001',
        '660e8400-e29b-41d4-a716-446655440010',
        5,
        'Invisible.',
    )))->toThrow(ProductNotFound::class);
});
