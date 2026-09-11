<?php

declare(strict_types=1);

namespace App\Payment\Application\QueryHandler;

use App\Payment\Application\Query\GetPaymentByOrderQuery;
use App\Payment\Application\Response\PaymentResponse;
use App\Payment\Domain\Exception\PaymentAccessForbidden;
use App\Payment\Domain\Exception\PaymentNotFound;
use App\Payment\Domain\Repository\PaymentRepository;
use App\Payment\Domain\ValueObject\CustomerReference;
use App\Payment\Domain\ValueObject\OrderReference;

final readonly class GetPaymentByOrderQueryHandler
{
    public function __construct(private PaymentRepository $paymentRepository)
    {
    }

    public function handle(GetPaymentByOrderQuery $query): PaymentResponse
    {
        $orderId = OrderReference::fromString($query->orderId);
        $customerId = CustomerReference::fromString($query->customerId);
        $payment = $this->paymentRepository->findByOrderId($orderId);

        if ($payment === null) {
            throw new PaymentNotFound($orderId);
        }

        if (!$payment->customerId()->equals($customerId)) {
            throw new PaymentAccessForbidden();
        }

        return PaymentResponse::fromPayment($payment);
    }
}
