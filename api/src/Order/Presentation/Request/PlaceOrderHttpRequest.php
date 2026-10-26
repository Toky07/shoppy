<?php

declare(strict_types=1);

namespace App\Order\Presentation\Request;

final readonly class PlaceOrderHttpRequest
{
    public function __construct(public OrderDeliveryHttpRequest $delivery)
    {
    }

    /**
     * @param array<string, mixed> $payload
     */
    public static function fromPayload(array $payload): self
    {
        return new self(OrderDeliveryHttpRequest::fromPayload($payload));
    }
}
