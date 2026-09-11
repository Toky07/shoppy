<?php

declare(strict_types=1);

namespace App\Auth\Infrastructure\Security;

use App\Auth\Application\Response\GeneratedAccessToken;
use App\Auth\Application\TokenGenerator;
use App\Auth\Domain\ValueObject\TokenHash;

final class RandomTokenGenerator implements TokenGenerator
{
    public function generate(): GeneratedAccessToken
    {
        $plain = bin2hex(random_bytes(32));

        return new GeneratedAccessToken($plain, $this->hash($plain));
    }

    public function hash(string $plainToken): TokenHash
    {
        return TokenHash::fromHash(hash('sha256', $plainToken));
    }
}
