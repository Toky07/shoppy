<?php

declare(strict_types=1);

namespace App\Order\Presentation\Request;

use App\Order\Application\Command\PlaceOrderAddress;
use App\Order\Domain\Exception\EmptyOrder;
use App\Shared\Presentation\Exception\InvalidRequest;

final readonly class OrderDeliveryHttpRequest
{
    public function __construct(
        public PlaceOrderAddress $shippingAddress,
        public PlaceOrderAddress $billingAddress,
    ) {
    }

    /**
     * @param array<string, mixed> $payload
     */
    public static function fromPayload(array $payload): self
    {
        if (!isset($payload['shippingAddress']) || !is_array($payload['shippingAddress'])) {
            throw InvalidRequest::of('This field is required.', 'shippingAddress');
        }

        $shipping = PostalAddressHttpRequest::fromPayload($payload['shippingAddress'], 'shippingAddress');
        $same = $payload['billingSameAsShipping'] ?? false;

        if (!is_bool($same)) {
            throw InvalidRequest::of('This value must be a boolean.', 'billingSameAsShipping');
        }

        if ($same) {
            $address = $shipping->toCommand();

            return new self($address, $address);
        }

        if (!isset($payload['billingAddress']) || !is_array($payload['billingAddress'])) {
            throw InvalidRequest::of('This field is required.', 'billingAddress');
        }

        return new self(
            $shipping->toCommand(),
            PostalAddressHttpRequest::fromPayload($payload['billingAddress'], 'billingAddress')->toCommand(),
        );
    }
}
