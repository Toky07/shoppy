<?php

declare(strict_types=1);

namespace App\Payment\Application\Response;

use App\Payment\Domain\Entity\Payment;
use DateTimeInterface;

final readonly class PaymentResponse
{
    public function __construct(
        public string $id,
        public string $orderId,
        public string $customerId,
        public int $amountCents,
        public string $status,
        public string $createdAt,
        public ?string $completedAt,
    ) {
    }

    public static function fromPayment(Payment $payment): self
    {
        return new self(
            $payment->id()->value(),
            $payment->orderId()->value(),
            $payment->customerId()->value(),
            $payment->amount()->cents(),
            $payment->status()->value(),
            $payment->createdAt()->format(DateTimeInterface::ATOM),
            $payment->completedAt()?->format(DateTimeInterface::ATOM),
        );
    }

    /**
     * @return array{
     *     id: string,
     *     orderId: string,
     *     customerId: string,
     *     amount: array{cents: int, currency: string},
     *     status: string,
     *     createdAt: string,
     *     completedAt: string|null
     * }
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'orderId' => $this->orderId,
            'customerId' => $this->customerId,
            'amount' => [
                'cents' => $this->amountCents,
                'currency' => 'EUR',
            ],
            'status' => $this->status,
            'createdAt' => $this->createdAt,
            'completedAt' => $this->completedAt,
        ];
    }
}
