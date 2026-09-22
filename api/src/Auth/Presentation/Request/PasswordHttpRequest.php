<?php

declare(strict_types=1);

namespace App\Auth\Presentation\Request;

use App\Shared\Presentation\Exception\InvalidRequest;

final readonly class PasswordHttpRequest
{
    public function __construct(public string $password)
    {
    }

    /**
     * @param array<string, mixed> $payload
     */
    public static function fromPayload(array $payload): self
    {
        if (!isset($payload['password']) || !is_string($payload['password'])) {
            throw InvalidRequest::of('This field is required.', 'password');
        }

        return new self($payload['password']);
    }
}
