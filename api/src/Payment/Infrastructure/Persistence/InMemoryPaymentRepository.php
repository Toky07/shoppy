<?php

declare(strict_types=1);

namespace App\Payment\Infrastructure\Persistence;

use App\Payment\Domain\Entity\Payment;
use App\Payment\Domain\Repository\PaymentRepository;
use App\Payment\Domain\ValueObject\OrderReference;

final class InMemoryPaymentRepository implements PaymentRepository
{
    /** @var array<string, Payment> */
    private array $byOrderId = [];

    public function save(Payment $payment): void
    {
        $this->byOrderId[$payment->orderId()->value()] = $payment;
    }

    public function findByOrderId(OrderReference $orderId): ?Payment
    {
        return $this->byOrderId[$orderId->value()] ?? null;
    }
}
