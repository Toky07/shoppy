<?php

declare(strict_types=1);

namespace App\Auth\Presentation\Http;

use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class SessionCookie
{
    private const LIFETIME = 60 * 60 * 24 * 7;

    public function attach(Response $response, Request $request, string $token): void
    {
        $response->headers->setCookie($this->cookie($request, $token, time() + self::LIFETIME));
    }

    public function clear(Response $response, Request $request): void
    {
        $response->headers->setCookie($this->cookie($request, '', 1));
    }

    private function cookie(Request $request, string $value, int $expires): Cookie
    {
        return Cookie::create(AccessCredential::COOKIE)
            ->withValue($value)
            ->withExpires($expires)
            ->withPath('/')
            ->withSecure($request->isSecure())
            ->withHttpOnly(true)
            ->withSameSite(Cookie::SAMESITE_LAX);
    }
}
