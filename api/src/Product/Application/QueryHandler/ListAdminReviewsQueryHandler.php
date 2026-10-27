<?php

declare(strict_types=1);

namespace App\Product\Application\QueryHandler;

use App\Product\Application\Port\ReviewAuthors;
use App\Product\Application\Query\ListAdminReviewsQuery;
use App\Product\Application\Response\AdminReviewListResponse;
use App\Product\Application\Response\AdminReviewResponse;
use App\Product\Domain\Entity\Product;
use App\Product\Domain\Entity\ProductReview;
use App\Product\Domain\Exception\InvalidReviewPagination;
use App\Product\Domain\Repository\ProductRepository;
use App\Product\Domain\Repository\ProductReviewRepository;

final readonly class ListAdminReviewsQueryHandler
{
    public function __construct(
        private ProductReviewRepository $reviewRepository,
        private ProductRepository $productRepository,
        private ReviewAuthors $reviewAuthors,
    ) {
    }

    public function handle(ListAdminReviewsQuery $query): AdminReviewListResponse
    {
        if ($query->page < 1 || $query->limit < 1 || $query->limit > ListAdminReviewsQuery::MAX_LIMIT) {
            throw new InvalidReviewPagination();
        }

        $offset = ($query->page - 1) * $query->limit;
        $reviews = $this->reviewRepository->findModerationPage($offset, $query->limit, $query->reportedOnly);
        $labels = $this->reviewAuthors->labelsFor(array_map(
            static fn (ProductReview $review): string => $review->authorId()->value(),
            $reviews,
        ));
        $products = $this->productsById($reviews);

        $items = array_map(
            static function (ProductReview $review) use ($labels, $products): AdminReviewResponse {
                $product = $products[$review->productId()->value()] ?? null;

                return AdminReviewResponse::fromReview(
                    $review,
                    $product?->name()->value() ?? 'Produit',
                    $product?->slug()->value() ?? '',
                    $labels[$review->authorId()->value()] ?? 'Client',
                );
            },
            $reviews,
        );

        return new AdminReviewListResponse(
            $items,
            $query->page,
            $query->limit,
            $this->reviewRepository->countModeration($query->reportedOnly),
        );
    }

    /**
     * @param list<ProductReview> $reviews
     *
     * @return array<string, Product>
     */
    private function productsById(array $reviews): array
    {
        $ids = [];

        foreach ($reviews as $review) {
            $ids[$review->productId()->value()] = $review->productId();
        }

        $products = [];

        foreach ($this->productRepository->findByIds(array_values($ids)) as $product) {
            $products[$product->id()->value()] = $product;
        }

        return $products;
    }
}
