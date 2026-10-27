<?php

declare(strict_types=1);

namespace App\Address\Application\Command;

final readonly class MarkDefaultAddressCommand
{
    public function __construct(
        public string $customerId,
        public string $addressId,
    ) {
    }
}
