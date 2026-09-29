<?php

declare(strict_types=1);

namespace App\Payment\Presentation\Security;

final readonly class PaymentAccessSubject
{
    public function __construct(public string $customerId)
    {
    }
}
