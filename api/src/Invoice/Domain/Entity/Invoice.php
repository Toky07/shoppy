<?php

declare(strict_types=1);

namespace App\Invoice\Domain\Entity;

use App\Invoice\Domain\ValueObject\CustomerReference;
use App\Invoice\Domain\ValueObject\InvoiceId;
use App\Invoice\Domain\ValueObject\InvoiceNumber;
use App\Invoice\Domain\ValueObject\MoneyAmount;
use App\Invoice\Domain\ValueObject\OrderReference;
use App\Invoice\Domain\ValueObject\PaymentReference;
use DateTimeImmutable;

final class Invoice
{
    private function __construct(
        private InvoiceId $id,
        private InvoiceNumber $number,
        private OrderReference $orderId,
        private CustomerReference $customerId,
        private PaymentReference $paymentId,
        private MoneyAmount $amount,
        private DateTimeImmutable $issuedAt,
    ) {
    }

    public static function issue(
        InvoiceId $id,
        InvoiceNumber $number,
        OrderReference $orderId,
        CustomerReference $customerId,
        PaymentReference $paymentId,
        MoneyAmount $amount,
        DateTimeImmutable $issuedAt,
    ): self {
        return new self($id, $number, $orderId, $customerId, $paymentId, $amount, $issuedAt);
    }

    public function id(): InvoiceId
    {
        return $this->id;
    }

    public function number(): InvoiceNumber
    {
        return $this->number;
    }

    public function orderId(): OrderReference
    {
        return $this->orderId;
    }

    public function customerId(): CustomerReference
    {
        return $this->customerId;
    }

    public function paymentId(): PaymentReference
    {
        return $this->paymentId;
    }

    public function amount(): MoneyAmount
    {
        return $this->amount;
    }

    public function issuedAt(): DateTimeImmutable
    {
        return $this->issuedAt;
    }
}
