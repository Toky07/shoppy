<?php

declare(strict_types=1);

namespace App\Tests\Doubles;

use App\Payment\Application\PaymentGateway;
use App\Payment\Application\Response\PaymentChargeResult;
use App\Payment\Domain\Entity\Payment;

final class FakePaymentGateway implements PaymentGateway
{
    public function __construct(private bool $succeeds = true, private string $failureReason = 'Card declined.')
    {
    }

    public function charge(Payment $payment): PaymentChargeResult
    {
        return $this->succeeds
            ? PaymentChargeResult::success()
            : PaymentChargeResult::failure($this->failureReason);
    }
}
