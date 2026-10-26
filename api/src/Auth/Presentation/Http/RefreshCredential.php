<?php

declare(strict_types=1);

namespace App\Auth\Presentation\Http;

use Symfony\Component\HttpFoundation\Request;

final class RefreshCredential
{
    public const COOKIE = 'shoppy_refresh';

    public static function fromRequest(Request $request): ?string
    {
        $cookie = $request->cookies->get(self::COOKIE);

        return is_string($cookie) && $cookie !== '' ? $cookie : null;
    }
}
