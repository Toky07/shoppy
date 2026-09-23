<?php

declare(strict_types=1);

namespace App\Payment\Infrastructure\Order;

use App\Order\Application\PendingPayment;
use App\Payment\Domain\Entity\Payment;
use App\Payment\Domain\Repository\PaymentRepository;
use App\Payment\Domain\ValueObject\CustomerReference;
use App\Payment\Domain\ValueObject\MoneyAmount;
use App\Payment\Domain\ValueObject\OrderReference;
use App\Payment\Domain\ValueObject\PaymentId;
use App\Shared\Domain\Clock;

final readonly class OpenPendingPayment implements PendingPayment
{
    public function __construct(
        private PaymentRepository $paymentRepository,
        private Clock $clock,
    ) {
    }

    public function open(string $orderId, string $customerId, int $amountCents): void
    {
        $reference = OrderReference::fromString($orderId);

        if ($this->paymentRepository->findByOrderId($reference) !== null) {
            return;
        }

        $this->paymentRepository->save(Payment::createPending(
            PaymentId::generate(),
            $reference,
            CustomerReference::fromString($customerId),
            MoneyAmount::fromCents($amountCents),
            $this->clock->now(),
        ));
    }
}
