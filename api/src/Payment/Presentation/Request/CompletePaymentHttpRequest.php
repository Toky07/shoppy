<?php

declare(strict_types=1);

namespace App\Payment\Presentation\Request;

use App\Shared\Presentation\Exception\InvalidRequest;

final readonly class CompletePaymentHttpRequest
{
    public function __construct(public string $orderId)
    {
    }

    /**
     * @param array<string, mixed> $payload
     */
    public static function fromPayload(array $payload): self
    {
        if (!isset($payload['orderId']) || !is_string($payload['orderId'])) {
            throw InvalidRequest::of('This field is required.', 'orderId');
        }

        return new self($payload['orderId']);
    }
}
