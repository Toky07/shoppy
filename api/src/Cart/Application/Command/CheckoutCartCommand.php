<?php

declare(strict_types=1);

namespace App\Cart\Application\Command;

final readonly class CheckoutCartCommand
{
    public function __construct(public string $customerId)
    {
    }
}
