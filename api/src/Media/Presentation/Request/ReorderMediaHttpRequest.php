<?php

declare(strict_types=1);

namespace App\Media\Presentation\Request;

use App\Shared\Presentation\Exception\InvalidRequest;

final readonly class ReorderMediaHttpRequest
{
    /**
     * @param list<string> $ids
     */
    public function __construct(
        public string $ownerType,
        public string $ownerId,
        public array $ids,
    ) {
    }

    /**
     * @param array<string, mixed> $payload
     */
    public static function fromPayload(array $payload): self
    {
        if (!isset($payload['ownerType']) || !is_string($payload['ownerType']) || trim($payload['ownerType']) === '') {
            throw InvalidRequest::of('This field is required.', 'ownerType');
        }

        if (!isset($payload['ownerId']) || !is_string($payload['ownerId']) || trim($payload['ownerId']) === '') {
            throw InvalidRequest::of('This field is required.', 'ownerId');
        }

        if (!isset($payload['ids']) || !is_array($payload['ids'])) {
            throw InvalidRequest::of('This field is required.', 'ids');
        }

        $ids = [];

        foreach ($payload['ids'] as $id) {
            if (!is_string($id) || $id === '') {
                throw InvalidRequest::of('This value must be a string.', 'ids');
            }

            $ids[] = $id;
        }

        return new self($payload['ownerType'], $payload['ownerId'], $ids);
    }
}
