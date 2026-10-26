<?php

declare(strict_types=1);

namespace App\Product\Infrastructure\Image;

use App\Product\Application\Port\HostAddresses;

final class DnsHostAddresses implements HostAddresses
{
    public function resolve(string $host): array
    {
        $addresses = gethostbynamel($host) ?: [];
        $records = dns_get_record($host, DNS_AAAA);

        if (is_array($records)) {
            foreach ($records as $record) {
                if (isset($record['ipv6']) && is_string($record['ipv6'])) {
                    $addresses[] = $record['ipv6'];
                }
            }
        }

        return array_values(array_unique($addresses));
    }
}
