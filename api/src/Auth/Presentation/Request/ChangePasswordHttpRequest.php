<?php

declare(strict_types=1);

namespace App\Auth\Presentation\Request;

use App\Shared\Presentation\Exception\InvalidRequest;

final readonly class ChangePasswordHttpRequest
{
    public function __construct(
        public string $currentPassword,
        public string $newPassword,
    ) {
    }

    /**
     * @param array<string, mixed> $payload
     */
    public static function fromPayload(array $payload): self
    {
        if (!isset($payload['currentPassword']) || !is_string($payload['currentPassword'])) {
            throw InvalidRequest::of('This field is required.', 'currentPassword');
        }

        if (!isset($payload['newPassword']) || !is_string($payload['newPassword'])) {
            throw InvalidRequest::of('This field is required.', 'newPassword');
        }

        return new self($payload['currentPassword'], $payload['newPassword']);
    }
}
