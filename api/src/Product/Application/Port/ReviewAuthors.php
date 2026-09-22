<?php

declare(strict_types=1);

namespace App\Product\Application\Port;

interface ReviewAuthors
{
    /**
     * @param list<string> $authorIds
     *
     * @return array<string, string>
     */
    public function labelsFor(array $authorIds): array;
}
