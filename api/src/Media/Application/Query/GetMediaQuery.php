<?php

declare(strict_types=1);

namespace App\Media\Application\Query;

final readonly class GetMediaQuery
{
    public function __construct(public string $id)
    {
    }
}
