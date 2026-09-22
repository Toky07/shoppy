<?php

declare(strict_types=1);

namespace App\Auth\Presentation\Request;

use App\Shared\Presentation\Exception\InvalidRequest;

final readonly class EmailChangeHttpRequest
{
    public function __construct(
        public string $email,
        public string $currentPassword,
    ) {
    }

    /**
     * @param array<string, mixed> $payload
     */
    public static function fromPayload(array $payload): self
    {
        if (!isset($payload['email']) || !is_string($payload['email'])) {
            throw InvalidRequest::of('This field is required.', 'email');
        }

        if (!isset($payload['currentPassword']) || !is_string($payload['currentPassword'])) {
            throw InvalidRequest::of('This field is required.', 'currentPassword');
        }

        return new self($payload['email'], $payload['currentPassword']);
    }
}
