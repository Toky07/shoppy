<?php

declare(strict_types=1);

namespace App\Cart\Presentation\Request;

use App\Order\Presentation\Request\OrderDeliveryHttpRequest;
use App\Shared\Presentation\Exception\InvalidRequest;

final readonly class CheckoutCartHttpRequest
{
    public function __construct(public OrderDeliveryHttpRequest $delivery)
    {
    }

    /**
     * @param array<string, mixed> $payload
     */
    public static function fromPayload(array $payload): self
    {
        if ($payload === []) {
            throw InvalidRequest::of('This field is required.', 'shippingAddress');
        }

        return new self(OrderDeliveryHttpRequest::fromPayload($payload));
    }
}
