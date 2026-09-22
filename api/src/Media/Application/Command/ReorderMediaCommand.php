<?php

declare(strict_types=1);

namespace App\Media\Application\Command;

final readonly class ReorderMediaCommand
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
}
