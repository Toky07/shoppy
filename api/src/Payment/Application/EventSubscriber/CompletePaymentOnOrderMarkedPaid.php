<?php

declare(strict_types=1);

namespace App\Payment\Application\EventSubscriber;

use App\Payment\Application\PaymentGatewayRegistry;
use App\Payment\Domain\Repository\PaymentRepository;
use App\Payment\Domain\ValueObject\OrderReference;
use App\Shared\Application\Event\OrderMarkedPaid;
use App\Shared\Domain\Clock;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final readonly class CompletePaymentOnOrderMarkedPaid implements EventSubscriberInterface
{
    public function __construct(
        private PaymentRepository $paymentRepository,
        private PaymentGatewayRegistry $gatewayRegistry,
        private Clock $clock,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [OrderMarkedPaid::class => 'onOrderMarkedPaid'];
    }

    public function onOrderMarkedPaid(OrderMarkedPaid $event): void
    {
        $payment = $this->paymentRepository->findByOrderId(
            OrderReference::fromString($event->orderId),
        );

        if ($payment === null || !$payment->status()->isPending()) {
            return;
        }

        if ($payment->customerId()->value() !== $event->customerId) {
            return;
        }

        $provider = $payment->provider();
        if ($provider !== null && $payment->providerReference() !== null) {
            $this->gatewayRegistry->get($provider)->expireCheckout($payment);
        }

        $payment->complete($this->clock->now());
        $this->paymentRepository->save($payment);
    }
}
