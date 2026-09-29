<?php

declare(strict_types=1);

namespace App\Order\Presentation\Security;

final readonly class OrderAccessSubject
{
    public function __construct(public string $customerId)
    {
    }
}
