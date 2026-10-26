<?php

declare(strict_types=1);

namespace App\Cart\Presentation\Request;

use App\Cart\Application\Command\GuestCheckoutLine;
use App\Order\Presentation\Request\OrderDeliveryHttpRequest;
use App\Shared\Presentation\Exception\InvalidRequest;

final readonly class CheckoutCartHttpRequest
{
    /**
     * @param list<GuestCheckoutLine> $items
     */
    public function __construct(
        public OrderDeliveryHttpRequest $delivery,
        public ?string $email = null,
        public array $items = [],
    ) {
    }

    /**
     * @param array<string, mixed> $payload
     */
    public static function fromPayload(array $payload): self
    {
        if ($payload === []) {
            throw InvalidRequest::of('This field is required.', 'shippingAddress');
        }

        return new self(
            OrderDeliveryHttpRequest::fromPayload($payload),
            self::email($payload),
            self::items($payload),
        );
    }

    /**
     * @param array<string, mixed> $payload
     */
    private static function email(array $payload): ?string
    {
        if (!array_key_exists('email', $payload) || $payload['email'] === null) {
            return null;
        }

        if (!is_string($payload['email']) || $payload['email'] === '') {
            throw InvalidRequest::of('This value must be a string.', 'email');
        }

        return $payload['email'];
    }

    /**
     * @param array<string, mixed> $payload
     *
     * @return list<GuestCheckoutLine>
     */
    private static function items(array $payload): array
    {
        if (!array_key_exists('items', $payload) || $payload['items'] === null) {
            return [];
        }

        if (!is_array($payload['items'])) {
            throw InvalidRequest::of('This value must be an array.', 'items');
        }

        $items = [];

        foreach ($payload['items'] as $index => $item) {
            if (!is_array($item)) {
                throw InvalidRequest::of('This value must be an object.', 'items.'.$index);
            }

            $items[] = new GuestCheckoutLine(
                self::productId($item, $index),
                self::quantity($item, $index),
                AddToCartHttpRequest::variantId($item),
            );
        }

        return $items;
    }

    /**
     * @param array<string, mixed> $item
     */
    private static function productId(array $item, int $index): string
    {
        if (!isset($item['productId']) || !is_string($item['productId'])) {
            throw InvalidRequest::of('This field is required.', 'items.'.$index.'.productId');
        }

        return $item['productId'];
    }

    /**
     * @param array<string, mixed> $item
     */
    private static function quantity(array $item, int $index): int
    {
        if (!isset($item['quantity']) || !is_int($item['quantity'])) {
            throw InvalidRequest::of('This field is required.', 'items.'.$index.'.quantity');
        }

        return $item['quantity'];
    }
}
