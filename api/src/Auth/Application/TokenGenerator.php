<?php

declare(strict_types=1);

namespace App\Auth\Application;

use App\Auth\Application\Response\GeneratedAccessToken;
use App\Auth\Domain\ValueObject\TokenHash;

interface TokenGenerator
{
    public function generate(): GeneratedAccessToken;

    public function hash(string $plainToken): TokenHash;
}
