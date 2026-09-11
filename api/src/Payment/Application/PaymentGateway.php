<?php

declare(strict_types=1);

namespace App\Payment\Application;

use App\Payment\Application\Response\PaymentChargeResult;
use App\Payment\Domain\Entity\Payment;

interface PaymentGateway
{
    public function charge(Payment $payment): PaymentChargeResult;
}
