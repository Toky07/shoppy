<?php

declare(strict_types=1);

namespace App\Auth\Application\Response;

use App\Auth\Domain\ValueObject\TokenHash;

final readonly class GeneratedAccessToken
{
    public function __construct(
        public string $plain,
        public TokenHash $hash,
    ) {
    }
}
