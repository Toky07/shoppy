<?php

declare(strict_types=1);

namespace App\Payment\Application\CommandHandler;

use App\Payment\Application\Command\StartCheckoutCommand;
use App\Payment\Application\PaymentGatewayRegistry;
use App\Payment\Application\Response\CheckoutContext;
use App\Payment\Application\Response\PaymentCheckoutResult;
use App\Payment\Domain\Exception\PaymentAccessForbidden;
use App\Payment\Domain\Exception\PaymentNotFound;
use App\Payment\Domain\Repository\PaymentRepository;
use App\Payment\Domain\ValueObject\CustomerReference;
use App\Payment\Domain\ValueObject\OrderReference;

final readonly class StartCheckoutCommandHandler
{
    public function __construct(
        private PaymentRepository $paymentRepository,
        private PaymentGatewayRegistry $gatewayRegistry,
    ) {
    }

    public function handle(StartCheckoutCommand $command): PaymentCheckoutResult
    {
        $orderId = OrderReference::fromString($command->orderId);
        $customerId = CustomerReference::fromString($command->customerId);
        $payment = $this->paymentRepository->findByOrderId($orderId);

        if ($payment === null) {
            throw new PaymentNotFound($orderId);
        }

        if (!$payment->customerId()->equals($customerId)) {
            throw new PaymentAccessForbidden();
        }

        $result = $this->gatewayRegistry->get($command->provider)->startCheckout(
            $payment,
            new CheckoutContext($command->successUrl, $command->cancelUrl),
        );

        $payment->attachCheckout($result->provider, $result->providerReference);
        $this->paymentRepository->save($payment);

        return $result;
    }
}
