<?php

declare(strict_types=1);

namespace App\Cart\Application\Command;

final readonly class MergeCartCommand
{
    /**
     * @param list<MergeCartLine> $items
     */
    public function __construct(
        public string $customerId,
        public array $items,
    ) {
    }
}
