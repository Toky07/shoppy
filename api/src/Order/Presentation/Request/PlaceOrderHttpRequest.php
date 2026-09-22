<?php

declare(strict_types=1);

namespace App\Order\Presentation\Request;

use App\Order\Domain\Exception\EmptyOrder;
use App\Order\Presentation\Request\OrderDeliveryHttpRequest;
use App\Shared\Presentation\Exception\InvalidRequest;

final readonly class PlaceOrderHttpRequest
{
    /**
     * @param list<array{productId: string, quantity: int}> $items
     */
    public function __construct(
        public array $items,
        public OrderDeliveryHttpRequest $delivery,
    ) {
    }

    /**
     * @param array<string, mixed> $payload
     */
    public static function fromPayload(array $payload): self
    {
        if (!isset($payload['items']) || !is_array($payload['items'])) {
            throw InvalidRequest::of('This field is required.', 'items');
        }

        $items = [];

        foreach ($payload['items'] as $item) {
            if (!is_array($item)) {
                throw InvalidRequest::of('This value must be an object.', 'items');
            }

            if (!isset($item['productId']) || !is_string($item['productId'])) {
                throw InvalidRequest::of('This field is required.', 'productId');
            }

            if (!isset($item['quantity']) || !is_int($item['quantity'])) {
                throw InvalidRequest::of('This field is required.', 'quantity');
            }

            $items[] = [
                'productId' => $item['productId'],
                'quantity' => $item['quantity'],
            ];
        }

        if ($items === []) {
            throw new EmptyOrder();
        }

        return new self($items, OrderDeliveryHttpRequest::fromPayload($payload));
    }
}
