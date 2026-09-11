<?php

declare(strict_types=1);

namespace App\Shared\Presentation\Http;

final readonly class MappedHttpError
{
    public function __construct(
        public ErrorResponse $body,
        public int $status,
        public bool $unexpected = false,
    ) {
    }
}
