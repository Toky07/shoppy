<?php

declare(strict_types=1);

namespace App\Cart\Presentation\Request;

use App\Shared\Presentation\Exception\InvalidRequest;

final readonly class UpdateCartItemHttpRequest
{
    public function __construct(public int $quantity)
    {
    }

    /**
     * @param array<string, mixed> $payload
     */
    public static function fromPayload(array $payload): self
    {
        if (!isset($payload['quantity']) || !is_int($payload['quantity'])) {
            throw InvalidRequest::of('This field is required.', 'quantity');
        }

        return new self($payload['quantity']);
    }
}
