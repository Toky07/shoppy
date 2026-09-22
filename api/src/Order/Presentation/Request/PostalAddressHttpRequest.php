<?php

declare(strict_types=1);

namespace App\Order\Presentation\Request;

use App\Order\Application\Command\PlaceOrderAddress;
use App\Shared\Presentation\Exception\InvalidRequest;

final readonly class PostalAddressHttpRequest
{
    public function __construct(
        public string $recipient,
        public string $line1,
        public ?string $line2,
        public string $postalCode,
        public string $city,
        public string $country,
    ) {
    }

    /**
     * @param array<string, mixed> $payload
     */
    public static function fromPayload(array $payload, string $field): self
    {
        return new self(
            self::requiredString($payload, 'recipient', $field),
            self::requiredString($payload, 'line1', $field),
            self::optionalString($payload, 'line2', $field),
            self::requiredString($payload, 'postalCode', $field),
            self::requiredString($payload, 'city', $field),
            self::requiredString($payload, 'country', $field),
        );
    }

    public function toCommand(): PlaceOrderAddress
    {
        return new PlaceOrderAddress(
            $this->recipient,
            $this->line1,
            $this->line2,
            $this->postalCode,
            $this->city,
            $this->country,
        );
    }

    /**
     * @param array<string, mixed> $payload
     */
    private static function requiredString(array $payload, string $key, string $field): string
    {
        if (!isset($payload[$key]) || !is_string($payload[$key])) {
            throw InvalidRequest::of('This field is required.', $field.'.'.$key);
        }

        return $payload[$key];
    }

    /**
     * @param array<string, mixed> $payload
     */
    private static function optionalString(array $payload, string $key, string $field): ?string
    {
        if (!array_key_exists($key, $payload) || $payload[$key] === null) {
            return null;
        }

        if (!is_string($payload[$key])) {
            throw InvalidRequest::of('This value must be a string.', $field.'.'.$key);
        }

        return $payload[$key];
    }
}
