<?php

declare(strict_types=1);

namespace App\Auth\Presentation\Http;

use App\Auth\Domain\Exception\Unauthenticated;

final class BearerToken
{
    public static function fromAuthorizationHeader(?string $header): string
    {
        if ($header === null || !str_starts_with($header, 'Bearer ')) {
            throw new Unauthenticated();
        }

        $token = substr($header, 7);

        if ($token === '') {
            throw new Unauthenticated();
        }

        return $token;
    }
}
