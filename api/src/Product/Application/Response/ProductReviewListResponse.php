<?php

declare(strict_types=1);

namespace App\Product\Application\Response;

final readonly class ProductReviewListResponse
{
    /**
     * @param list<ProductReviewResponse> $items
     */
    public function __construct(
        public array $items,
        public int $page,
        public int $limit,
        public int $count,
        public ?float $averageRating,
    ) {
    }

    /**
     * @return array{items: list<array<string, mixed>>, page: int, limit: int, count: int, averageRating: float|null}
     */
    public function toArray(): array
    {
        return [
            'items' => array_map(
                static fn (ProductReviewResponse $item): array => $item->toArray(),
                $this->items,
            ),
            'page' => $this->page,
            'limit' => $this->limit,
            'count' => $this->count,
            'averageRating' => $this->averageRating,
        ];
    }
}
