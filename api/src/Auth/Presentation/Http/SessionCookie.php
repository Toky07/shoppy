<?php

declare(strict_types=1);

namespace App\Auth\Presentation\Http;

use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class SessionCookie
{
    private const ACCESS_LIFETIME = 60 * 60;

    private const REFRESH_LIFETIME = 60 * 60 * 24 * 7;

    public function attach(Response $response, Request $request, string $accessToken, string $refreshToken): void
    {
        $now = time();
        $response->headers->setCookie($this->cookie(
            $request,
            AccessCredential::COOKIE,
            $accessToken,
            $now + self::ACCESS_LIFETIME,
        ));
        $response->headers->setCookie($this->cookie(
            $request,
            RefreshCredential::COOKIE,
            $refreshToken,
            $now + self::REFRESH_LIFETIME,
        ));
    }

    public function clear(Response $response, Request $request): void
    {
        $response->headers->setCookie($this->cookie($request, AccessCredential::COOKIE, '', 1));
        $response->headers->setCookie($this->cookie($request, RefreshCredential::COOKIE, '', 1));
    }

    private function cookie(Request $request, string $name, string $value, int $expires): Cookie
    {
        return Cookie::create($name)
            ->withValue($value)
            ->withExpires($expires)
            ->withPath('/')
            ->withSecure($request->isSecure())
            ->withHttpOnly(true)
            ->withSameSite(Cookie::SAMESITE_LAX);
    }
}
