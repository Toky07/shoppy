<?php

declare(strict_types=1);

namespace App\Favorite\Presentation\Request;

use App\Shared\Presentation\Exception\InvalidRequest;

final readonly class MergeFavoritesHttpRequest
{
    /**
     * @param list<string> $productIds
     */
    public function __construct(public array $productIds)
    {
    }

    /**
     * @param array<string, mixed> $payload
     */
    public static function fromPayload(array $payload): self
    {
        if (!isset($payload['productIds']) || !is_array($payload['productIds'])) {
            throw InvalidRequest::of('This field is required.', 'productIds');
        }

        $productIds = [];

        foreach ($payload['productIds'] as $index => $productId) {
            if (!is_string($productId)) {
                throw InvalidRequest::of('This value must be a string.', 'productIds.'.$index);
            }

            $productIds[] = $productId;
        }

        return new self($productIds);
    }
}
