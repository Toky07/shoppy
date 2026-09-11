<?php

declare(strict_types=1);

namespace App\Product\Presentation\Request;

use App\Shared\Presentation\Exception\InvalidRequest;

final readonly class SetProductStockHttpRequest
{
    public function __construct(public int $stock)
    {
    }

    /**
     * @param array<string, mixed> $payload
     */
    public static function fromPayload(array $payload): self
    {
        if (!isset($payload['stock']) || !is_int($payload['stock'])) {
            throw InvalidRequest::of('This field is required.', 'stock');
        }

        return new self($payload['stock']);
    }
}
