<?php

declare(strict_types=1);

namespace App\Media\Application\Query;

final readonly class ListMediaByOwnerQuery
{
    public function __construct(
        public string $ownerType,
        public string $ownerId,
    ) {
    }
}
