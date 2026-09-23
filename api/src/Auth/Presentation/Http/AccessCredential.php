<?php

declare(strict_types=1);

namespace App\Auth\Presentation\Http;

use Symfony\Component\HttpFoundation\Request;

final class AccessCredential
{
    public const COOKIE = 'shoppy_session';

    public static function fromRequest(Request $request): ?string
    {
        $header = $request->headers->get('Authorization');

        if (is_string($header) && str_starts_with($header, 'Bearer ')) {
            $token = substr($header, 7);

            if ($token !== '') {
                return $token;
            }
        }

        $cookie = $request->cookies->get(self::COOKIE);

        return is_string($cookie) && $cookie !== '' ? $cookie : null;
    }
}
