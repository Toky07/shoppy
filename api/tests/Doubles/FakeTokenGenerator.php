<?php

declare(strict_types=1);

namespace App\Tests\Doubles;

use App\Auth\Application\Response\GeneratedAccessToken;
use App\Auth\Application\TokenGenerator;
use App\Auth\Domain\ValueObject\TokenHash;

final class FakeTokenGenerator implements TokenGenerator
{
    public function __construct(private string $plain = 'test-access-token')
    {
    }

    public function generate(): GeneratedAccessToken
    {
        return new GeneratedAccessToken($this->plain, $this->hash($this->plain));
    }

    public function hash(string $plainToken): TokenHash
    {
        return TokenHash::fromHash(hash('sha256', $plainToken));
    }
}
