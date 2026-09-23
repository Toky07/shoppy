<?php

declare(strict_types=1);

namespace App\Payment\Application;

use App\Payment\Domain\Exception\CheckoutUrlNotAllowed;

final readonly class AllowedCheckoutUrl
{
    public function __construct(private string $allowedOrigins)
    {
    }

    public function assertAllowed(string $url, string $field): void
    {
        $parts = parse_url($url);

        if (
            !is_array($parts)
            || !isset($parts['scheme'], $parts['host'])
            || isset($parts['user'])
            || isset($parts['pass'])
        ) {
            throw new CheckoutUrlNotAllowed($field);
        }

        $scheme = strtolower((string) $parts['scheme']);

        if ($scheme !== 'http' && $scheme !== 'https') {
            throw new CheckoutUrlNotAllowed($field);
        }

        $origin = $scheme.'://'.strtolower((string) $parts['host']);

        if (isset($parts['port'])) {
            $origin .= ':'.$parts['port'];
        }

        foreach ($this->origins() as $allowed) {
            if ($allowed === $origin) {
                return;
            }
        }

        throw new CheckoutUrlNotAllowed($field);
    }

    /**
     * @return list<string>
     */
    private function origins(): array
    {
        $origins = [];

        foreach (explode(',', $this->allowedOrigins) as $candidate) {
            $candidate = rtrim(trim($candidate), '/');

            if ($candidate === '') {
                continue;
            }

            $origins[] = strtolower($candidate);
        }

        return $origins;
    }
}
