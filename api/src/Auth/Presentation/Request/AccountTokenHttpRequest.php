<?php

declare(strict_types=1);

namespace App\Auth\Presentation\Request;

use App\Shared\Presentation\Exception\InvalidRequest;

final readonly class AccountTokenHttpRequest
{
    public function __construct(public string $token)
    {
    }

    /**
     * @param array<string, mixed> $payload
     */
    public static function fromPayload(array $payload): self
    {
        if (!isset($payload['token']) || !is_string($payload['token']) || $payload['token'] === '') {
            throw InvalidRequest::of('This field is required.', 'token');
        }

        return new self($payload['token']);
    }
}
