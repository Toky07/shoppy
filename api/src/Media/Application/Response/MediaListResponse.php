<?php

declare(strict_types=1);

namespace App\Media\Application\Response;

final readonly class MediaListResponse
{
    /**
     * @param list<MediaResponse> $items
     */
    public function __construct(public array $items)
    {
    }

    /**
     * @return array{items: list<array<string, mixed>>}
     */
    public function toArray(): array
    {
        return [
            'items' => array_map(
                static fn (MediaResponse $item): array => $item->toArray(),
                $this->items,
            ),
        ];
    }
}
