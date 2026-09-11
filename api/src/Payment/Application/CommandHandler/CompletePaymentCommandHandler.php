<?php

declare(strict_types=1);

namespace App\Payment\Application\CommandHandler;

use App\Payment\Application\Command\CompletePaymentCommand;
use App\Payment\Application\PaymentGateway;
use App\Payment\Domain\Exception\PaymentAccessForbidden;
use App\Payment\Domain\Exception\PaymentChargeFailed;
use App\Payment\Domain\Exception\PaymentNotFound;
use App\Payment\Domain\Repository\PaymentRepository;
use App\Payment\Domain\ValueObject\CustomerReference;
use App\Payment\Domain\ValueObject\OrderReference;
use App\Shared\Application\Event\PaymentCompleted;
use App\Shared\Domain\Clock;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

final readonly class CompletePaymentCommandHandler
{
    public function __construct(
        private PaymentRepository $paymentRepository,
        private PaymentGateway $paymentGateway,
        private Clock $clock,
        private EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function handle(CompletePaymentCommand $command): void
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

        $charge = $this->paymentGateway->charge($payment);
        if (!$charge->successful) {
            throw new PaymentChargeFailed($charge->failureReason ?? 'Payment charge failed.');
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
