<?php

declare(strict_types=1);

namespace App\Payment\Domain\Repository;

use App\Payment\Domain\Entity\Payment;
use App\Payment\Domain\ValueObject\OrderReference;

interface PaymentRepository
{
    public function save(Payment $payment): void;

    public function findByOrderId(OrderReference $orderId): ?Payment;

    public function findByProviderReference(string $providerReference): ?Payment;
}
