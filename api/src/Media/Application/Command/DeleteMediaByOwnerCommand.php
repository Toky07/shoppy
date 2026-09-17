<?php

declare(strict_types=1);

namespace App\Media\Application\Command;

final readonly class DeleteMediaByOwnerCommand
{
    public function __construct(
        public string $ownerType,
        public string $ownerId,
    ) {
    }
}
