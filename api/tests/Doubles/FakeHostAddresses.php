<?php

declare(strict_types=1);

namespace App\Tests\Doubles;

use App\Product\Application\Port\HostAddresses;

final class FakeHostAddresses implements HostAddresses
{
    /**
     * @param array<string, list<string>> $hosts
     */
    public function __construct(private array $hosts = [])
    {
    }

    public function resolve(string $host): array
    {
        return $this->hosts[strtolower($host)] ?? [];
    }
}
