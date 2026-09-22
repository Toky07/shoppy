<?php

declare(strict_types=1);

namespace App\Product\Presentation\Request;

use App\Shared\Presentation\Exception\InvalidRequest;

final readonly class SubmitProductReviewHttpRequest
{
    public function __construct(
        public int $rating,
        public string $body,
    ) {
    }

    /**
     * @param array<string, mixed> $payload
     */
    public static function fromPayload(array $payload): self
    {
        if (!isset($payload['rating']) || !is_int($payload['rating'])) {
            throw InvalidRequest::of('This field is required.', 'rating');
        }

        if (!isset($payload['body']) || !is_string($payload['body'])) {
            throw InvalidRequest::of('This field is required.', 'body');
        }

        return new self($payload['rating'], $payload['body']);
    }
}
