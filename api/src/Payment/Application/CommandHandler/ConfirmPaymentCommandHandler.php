<?php

declare(strict_types=1);

namespace App\Payment\Application\CommandHandler;

use App\Payment\Application\Command\ConfirmPaymentCommand;
use App\Payment\Application\PaymentGatewayRegistry;
use App\Payment\Application\Port\PayableOrder;
use App\Payment\Domain\Exception\PaymentAccessForbidden;
use App\Payment\Domain\Exception\PaymentAmountMismatch;
use App\Payment\Domain\Exception\PaymentNotFound;
use App\Payment\Domain\Repository\PaymentRepository;
use App\Payment\Domain\ValueObject\CustomerReference;
use App\Payment\Domain\ValueObject\OrderReference;
use App\Shared\Application\Event\PaymentCompleted;
use App\Shared\Domain\Clock;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

final readonly class ConfirmPaymentCommandHandler
{
    public function __construct(
        private PaymentRepository $paymentRepository,
        private PaymentGatewayRegistry $gatewayRegistry,
        private Clock $clock,
        private EventDispatcherInterface $eventDispatcher,
        private PayableOrder $payableOrder,
    ) {
    }

    public function handle(ConfirmPaymentCommand $command): void
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

        if (!$payment->status()->isPending() || !$this->payableOrder->isPending($command->orderId)) {
            return;
        }

        $provider = $payment->provider() ?? $this->gatewayRegistry->defaultName();
        $session = $this->gatewayRegistry->get($provider)->hostedCheckout($payment);
        if ($session === null || !$session->paid) {
            return;
        }

        if ($session->amountCents !== $payment->amount()->cents()) {
            throw new PaymentAmountMismatch();
        }

        $payment->complete($this->clock->now());
        $this->paymentRepository->save($payment);

        $this->eventDispatcher->dispatch(new PaymentCompleted(
            paymentId: $payment->id()->value(),
            orderId: $payment->orderId()->value(),
            customerId: $payment->customerId()->value(),
            amountCents: $payment->amount()->cents(),
        ));
    }
}
