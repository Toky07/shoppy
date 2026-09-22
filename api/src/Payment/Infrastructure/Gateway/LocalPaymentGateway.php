<?php

declare(strict_types=1);

namespace App\Payment\Infrastructure\Gateway;

use App\Payment\Application\PaymentGateway;
use App\Payment\Application\Response\CheckoutContext;
use App\Payment\Application\Response\PaymentChargeResult;
use App\Payment\Application\Response\PaymentCheckoutResult;
use App\Payment\Domain\Entity\Payment;

/**
 * Local in-process gateway: always accepts the charge.
 * Swap this binding for a real provider later without touching the domain.
 */
final class LocalPaymentGateway implements PaymentGateway
{
    public const NAME = 'local';

    public function name(): string
    {
        return self::NAME;
    }

    public function charge(Payment $payment): PaymentChargeResult
    {
        return PaymentChargeResult::success();
    }

    public function startCheckout(Payment $payment, CheckoutContext $context): PaymentCheckoutResult
    {
        return PaymentCheckoutResult::immediate(self::NAME);
    }

    public function expireCheckout(Payment $payment): void
    {
    }
}
