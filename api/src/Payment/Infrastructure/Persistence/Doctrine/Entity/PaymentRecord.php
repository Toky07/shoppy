<?php

declare(strict_types=1);

namespace App\Payment\Infrastructure\Persistence\Doctrine\Entity;

use App\Payment\Domain\Entity\Payment;
use App\Payment\Domain\ValueObject\CustomerReference;
use App\Payment\Domain\ValueObject\MoneyAmount;
use App\Payment\Domain\ValueObject\OrderReference;
use App\Payment\Domain\ValueObject\PaymentId;
use App\Payment\Domain\ValueObject\PaymentStatus;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'payments')]
#[ORM\UniqueConstraint(name: 'uniq_payments_order_id', columns: ['order_id'])]
#[ORM\Index(name: 'idx_payments_provider_reference', columns: ['provider_reference'])]
class PaymentRecord
{
    #[ORM\Id]
    #[ORM\Column(length: 36)]
    private string $id;

    #[ORM\Column(name: 'order_id', length: 36)]
    private string $orderId;

    #[ORM\Column(name: 'customer_id', length: 36)]
    private string $customerId;

    #[ORM\Column(name: 'amount_cents')]
    private int $amountCents;

    #[ORM\Column(length: 32)]
    private string $status;

    #[ORM\Column(name: 'created_at')]
    private DateTimeImmutable $createdAt;

    #[ORM\Column(name: 'completed_at', nullable: true)]
    private ?DateTimeImmutable $completedAt = null;

    #[ORM\Column(length: 32, nullable: true)]
    private ?string $provider = null;

    #[ORM\Column(name: 'provider_reference', length: 255, nullable: true)]
    private ?string $providerReference = null;

    public static function fromDomain(Payment $payment): self
    {
        $record = new self();
        $record->apply($payment);

        return $record;
    }

    public function updateFromDomain(Payment $payment): void
    {
        $this->status = $payment->status()->value();
        $this->completedAt = $payment->completedAt();
        $this->provider = $payment->provider();
        $this->providerReference = $payment->providerReference();
    }

    public function toDomain(): Payment
    {
        return Payment::reconstitute(
            PaymentId::fromString($this->id),
            OrderReference::fromString($this->orderId),
            CustomerReference::fromString($this->customerId),
            MoneyAmount::fromCents($this->amountCents),
            PaymentStatus::fromString($this->status),
            $this->createdAt,
            $this->completedAt,
            $this->provider,
            $this->providerReference,
        );
    }

    private function apply(Payment $payment): void
    {
        $this->id = $payment->id()->value();
        $this->orderId = $payment->orderId()->value();
        $this->customerId = $payment->customerId()->value();
        $this->amountCents = $payment->amount()->cents();
        $this->status = $payment->status()->value();
        $this->createdAt = $payment->createdAt();
        $this->completedAt = $payment->completedAt();
        $this->provider = $payment->provider();
        $this->providerReference = $payment->providerReference();
    }
}
