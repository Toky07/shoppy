<?php

declare(strict_types=1);

namespace App\Payment\Application\CommandHandler;

use App\Payment\Application\Command\HandleStripeWebhookCommand;
use App\Payment\Domain\Exception\PaymentAmountMismatch;
use App\Payment\Domain\Repository\PaymentRepository;
use App\Shared\Application\Event\PaymentCompleted;
use App\Shared\Domain\Clock;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

final readonly class HandleStripeWebhookCommandHandler
{
    public const CHECKOUT_SESSION_COMPLETED = 'checkout.session.completed';

    public function __construct(
        private PaymentRepository $paymentRepository,
        private Clock $clock,
        private EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function handle(HandleStripeWebhookCommand $command): void
    {
        if ($command->type !== self::CHECKOUT_SESSION_COMPLETED) {
            return;
        }

        $payment = $this->paymentRepository->findByProviderReference($command->providerReference);

        if ($payment === null || !$payment->status()->isPending()) {
            return;
        }

        if ($command->amountCents !== $payment->amount()->cents()) {
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
