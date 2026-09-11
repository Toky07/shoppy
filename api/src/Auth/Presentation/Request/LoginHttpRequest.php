<?php

declare(strict_types=1);

namespace App\Auth\Presentation\Request;

use App\Shared\Presentation\Exception\InvalidRequest;

final readonly class LoginHttpRequest
{
    public function __construct(
        public string $email,
        public string $password,
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

        if (!isset($payload['password']) || !is_string($payload['password'])) {
            throw InvalidRequest::of('This field is required.', 'password');
        }

        return new self($payload['email'], $payload['password']);
    }
}
