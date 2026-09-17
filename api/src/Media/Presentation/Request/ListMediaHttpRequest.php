<?php

declare(strict_types=1);

namespace App\Media\Presentation\Request;

use App\Shared\Presentation\Exception\InvalidRequest;
use Symfony\Component\HttpFoundation\Request;

final readonly class ListMediaHttpRequest
{
    public function __construct(
        public string $ownerType,
        public string $ownerId,
    ) {
    }

    public static function fromRequest(Request $request): self
    {
        $ownerType = $request->query->get('ownerType');
        if (!is_string($ownerType) || trim($ownerType) === '') {
            throw InvalidRequest::of('This field is required.', 'ownerType');
        }

        $ownerId = $request->query->get('ownerId');
        if (!is_string($ownerId) || trim($ownerId) === '') {
            throw InvalidRequest::of('This field is required.', 'ownerId');
        }

        return new self($ownerType, $ownerId);
    }
}
