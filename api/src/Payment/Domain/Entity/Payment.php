<?php

declare(strict_types=1);

namespace App\Payment\Domain\Entity;

use App\Payment\Domain\Exception\PaymentAlreadyCompleted;
use App\Payment\Domain\Exception\PaymentNotPayable;
use App\Payment\Domain\ValueObject\CustomerReference;
use App\Payment\Domain\ValueObject\MoneyAmount;
use App\Payment\Domain\ValueObject\OrderReference;
use App\Payment\Domain\ValueObject\PaymentId;
use App\Payment\Domain\ValueObject\PaymentStatus;
use DateTimeImmutable;

final class Payment
{
    private function __construct(
        private PaymentId $id,
        private OrderReference $orderId,
        private CustomerReference $customerId,
        private MoneyAmount $amount,
        private PaymentStatus $status,
        private DateTimeImmutable $createdAt,
        private ?DateTimeImmutable $completedAt,
    ) {
    }

    public static function createPending(
        PaymentId $id,
        OrderReference $orderId,
        CustomerReference $customerId,
        MoneyAmount $amount,
        DateTimeImmutable $createdAt,
    ): self {
        return new self($id, $orderId, $customerId, $amount, PaymentStatus::pending(), $createdAt, null);
    }

    public static function reconstitute(
        PaymentId $id,
        OrderReference $orderId,
        CustomerReference $customerId,
        MoneyAmount $amount,
        PaymentStatus $status,
        DateTimeImmutable $createdAt,
        ?DateTimeImmutable $completedAt,
    ): self {
        return new self($id, $orderId, $customerId, $amount, $status, $createdAt, $completedAt);
    }

    public function id(): PaymentId
    {
        return $this->id;
    }

    public function orderId(): OrderReference
    {
        return $this->orderId;
    }

    public function customerId(): CustomerReference
    {
        return $this->customerId;
    }

    public function amount(): MoneyAmount
    {
        return $this->amount;
    }

    public function status(): PaymentStatus
    {
        return $this->status;
    }

    public function createdAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function completedAt(): ?DateTimeImmutable
    {
        return $this->completedAt;
    }

    public function complete(DateTimeImmutable $completedAt): void
    {
        if ($this->status->isCompleted()) {
            throw new PaymentAlreadyCompleted();
        }

        if (!$this->status->isPending()) {
            throw new PaymentNotPayable();
        }

        $this->status = PaymentStatus::completed();
        $this->completedAt = $completedAt;
    }

    public function cancel(): void
    {
        if ($this->status->isCancelled()) {
            return;
        }

        if ($this->status->isCompleted()) {
            return;
        }

        $this->status = PaymentStatus::cancelled();
    }
}
