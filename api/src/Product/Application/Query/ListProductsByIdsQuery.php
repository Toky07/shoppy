<?php

declare(strict_types=1);

namespace App\Product\Application\Query;

final readonly class ListProductsByIdsQuery
{
    public const MAX_IDS = 100;

    /**
     * @param list<string> $ids
     */
    public function __construct(public array $ids)
    {
    }
}
