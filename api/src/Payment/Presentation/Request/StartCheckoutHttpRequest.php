<?php

declare(strict_types=1);

namespace App\Payment\Presentation\Request;

use App\Shared\Presentation\Exception\InvalidRequest;

final readonly class StartCheckoutHttpRequest
{
    public function __construct(
        public string $orderId,
        public string $provider,
        public string $successUrl,
        public string $cancelUrl,
    ) {
    }

    /**
     * @param array<string, mixed> $payload
     */
    public static function fromPayload(array $payload): self
    {
        return new self(
            self::requiredString($payload, 'orderId'),
            self::requiredString($payload, 'provider'),
            self::requiredString($payload, 'successUrl'),
            self::requiredString($payload, 'cancelUrl'),
        );
    }

    /**
     * @param array<string, mixed> $payload
     */
    private static function requiredString(array $payload, string $field): string
    {
        if (!isset($payload[$field]) || !is_string($payload[$field])) {
            throw InvalidRequest::of('This field is required.', $field);
        }

        return $payload[$field];
    }
}
