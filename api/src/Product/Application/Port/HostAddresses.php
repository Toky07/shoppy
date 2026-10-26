<?php

declare(strict_types=1);

namespace App\Product\Application\Port;

interface HostAddresses
{
    /**
     * @return list<string>
     */
    public function resolve(string $host): array;
}
