<?php

declare(strict_types=1);

namespace App\User\Presentation\Request;

use App\Shared\Presentation\Exception\InvalidRequest;

final readonly class AssignUserRoleHttpRequest
{
    public function __construct(public string $role)
    {
    }

    /**
     * @param array<string, mixed> $payload
     */
    public static function fromPayload(array $payload): self
    {
        if (!isset($payload['role']) || !is_string($payload['role'])) {
            throw InvalidRequest::of('This field is required.', 'role');
        }

        return new self($payload['role']);
    }
}
