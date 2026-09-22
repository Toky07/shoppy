<?php

declare(strict_types=1);

namespace App\Order\Application\Command;

final readonly class PlaceOrderAddress
{
    public function __construct(
        public string $recipient,
        public string $line1,
        public ?string $line2,
        public string $postalCode,
        public string $city,
        public string $country,
    ) {
    }
}
