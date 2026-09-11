<?php

declare(strict_types=1);

namespace App\Cart\Presentation\Request;

use App\Shared\Presentation\Exception\InvalidRequest;

final readonly class AddToCartHttpRequest
{
    public function __construct(
        public string $productId,
        public int $quantity,
    ) {
    }

    /**
     * @param array<string, mixed> $payload
     */
    public static function fromPayload(array $payload): self
    {
        if (!isset($payload['productId']) || !is_string($payload['productId'])) {
            throw InvalidRequest::of('This field is required.', 'productId');
        }

        if (!isset($payload['quantity']) || !is_int($payload['quantity'])) {
            throw InvalidRequest::of('This field is required.', 'quantity');
        }

        return new self($payload['productId'], $payload['quantity']);
    }
}
