<?php

declare(strict_types=1);

namespace App\Product\Application\Image;

use App\Product\Application\Port\HostAddresses;
use App\Product\Domain\Exception\RemoteImageUrlNotAllowed;

final readonly class AllowedRemoteImageUrl
{
    public function __construct(private HostAddresses $hosts)
    {
    }

    public function assertSafe(string $url): void
    {
        $parts = parse_url($url);

        if (
            !is_array($parts)
            || strtolower((string) ($parts['scheme'] ?? '')) !== 'https'
            || !isset($parts['host'])
            || trim((string) $parts['host']) === ''
            || isset($parts['user'])
            || isset($parts['pass'])
        ) {
            throw new RemoteImageUrlNotAllowed();
        }

        if (isset($parts['port']) && (int) $parts['port'] !== 443) {
            throw new RemoteImageUrlNotAllowed();
        }

        $host = strtolower(rtrim((string) $parts['host'], '.'));
        $host = trim($host, '[]');

        foreach ($this->addressesFor($host) as $address) {
            if (!$this->isPublicAddress($address)) {
                throw new RemoteImageUrlNotAllowed();
            }
        }
    }

    /**
     * @return list<string>
     */
    private function addressesFor(string $host): array
    {
        if ($this->isIpAddress($host)) {
            return [$host];
        }

        $resolved = $this->hosts->resolve($host);

        if ($resolved === []) {
            throw new RemoteImageUrlNotAllowed();
        }

        return $resolved;
    }

    private function isIpAddress(string $host): bool
    {
        return filter_var($host, FILTER_VALIDATE_IP) !== false;
    }

    private function isPublicAddress(string $address): bool
    {
        if (preg_match('/^::ffff:(.+)$/i', $address, $matches) === 1) {
            $address = $matches[1];
        }

        $flags = FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE;

        return filter_var($address, FILTER_VALIDATE_IP, $flags) !== false;
    }
}
