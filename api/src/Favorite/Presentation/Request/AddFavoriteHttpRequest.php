<?php

declare(strict_types=1);

namespace App\Favorite\Presentation\Request;

use App\Shared\Presentation\Exception\InvalidRequest;

final readonly class AddFavoriteHttpRequest
{
    public function __construct(public string $productId)
    {
    }

    /**
     * @param array<string, mixed> $payload
     */
    public static function fromPayload(array $payload): self
    {
        if (!isset($payload['productId']) || !is_string($payload['productId'])) {
            throw InvalidRequest::of('This field is required.', 'productId');
        }

        return new self($payload['productId']);
    }
}
