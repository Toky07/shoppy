<?php

declare(strict_types=1);

namespace App\User\Application\Response;

final readonly class UserListResponse
{
    /**
     * @param list<UserResponse> $items
     */
    public function __construct(
        public array $items,
        public int $page,
        public int $limit,
        public int $total,
    ) {
    }

    /**
     * @return array{
     *     items: list<array{id: string, email: string, createdAt: string, role: string}>,
     *     page: int,
     *     limit: int,
     *     total: int
     * }
     */
    public function toArray(): array
    {
        return [
            'items' => array_map(
                static fn (UserResponse $item): array => $item->toArray(),
                $this->items,
            ),
            'page' => $this->page,
            'limit' => $this->limit,
            'total' => $this->total,
        ];
    }
}
