<?php

declare(strict_types=1);

namespace App\Tests\Doubles;

use App\Payment\Application\PaymentGateway;
use App\Payment\Application\Response\CheckoutContext;
use App\Payment\Application\Response\PaymentChargeResult;
use App\Payment\Application\Response\PaymentCheckoutResult;
use App\Payment\Domain\Entity\Payment;

final class FakePaymentGateway implements PaymentGateway
{
    public function __construct(
        private bool $succeeds = true,
        private string $failureReason = 'Card declined.',
        private string $name = 'fake',
    ) {
    }

    public function name(): string
    {
        return $this->name;
    }

    public function charge(Payment $payment): PaymentChargeResult
    {
        return $this->succeeds
            ? PaymentChargeResult::success()
            : PaymentChargeResult::failure($this->failureReason);
    }

    public function startCheckout(Payment $payment, CheckoutContext $context): PaymentCheckoutResult
    {
        return PaymentCheckoutResult::immediate($this->name);
    }
}
