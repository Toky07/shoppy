<?php

declare(strict_types=1);

namespace App\Tests\Doubles;

use App\Auth\Application\Response\GeneratedAccessToken;
use App\Auth\Application\TokenGenerator;
use App\Auth\Domain\ValueObject\TokenHash;

final class FakeTokenGenerator implements TokenGenerator
{
    private int $issued = 0;

    public function __construct(private string $plain = 'test-access-token')
    {
    }

    public function generate(): GeneratedAccessToken
    {
        $plain = $this->issued === 0 ? $this->plain : $this->plain.'-'.$this->issued;
        ++$this->issued;

        return new GeneratedAccessToken($plain, $this->hash($plain));
    }

    public function hash(string $plainToken): TokenHash
    {
        return TokenHash::fromHash(hash('sha256', $plainToken));
    }
}
