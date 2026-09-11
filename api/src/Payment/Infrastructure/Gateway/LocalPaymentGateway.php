<?php

declare(strict_types=1);

namespace App\Payment\Infrastructure\Gateway;

use App\Payment\Application\PaymentGateway;
use App\Payment\Application\Response\PaymentChargeResult;
use App\Payment\Domain\Entity\Payment;

/**
 * Local in-process gateway: always accepts the charge.
 * Swap this binding for a real provider later without touching the domain.
 */
final class LocalPaymentGateway implements PaymentGateway
{
    public function charge(Payment $payment): PaymentChargeResult
    {
        return PaymentChargeResult::success();
    }
}
