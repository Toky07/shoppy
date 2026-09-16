<?php

declare(strict_types=1);

namespace App\Payment\Application;

use App\Payment\Application\Response\CheckoutContext;
use App\Payment\Application\Response\PaymentChargeResult;
use App\Payment\Application\Response\PaymentCheckoutResult;
use App\Payment\Domain\Entity\Payment;

interface PaymentGateway
{
    public function name(): string;

    public function charge(Payment $payment): PaymentChargeResult;

    public function startCheckout(Payment $payment, CheckoutContext $context): PaymentCheckoutResult;
}
