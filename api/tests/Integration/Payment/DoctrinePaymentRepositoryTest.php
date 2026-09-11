<?php

declare(strict_types=1);

use App\Payment\Domain\Entity\Payment;
use App\Payment\Domain\Repository\PaymentRepository;
use App\Payment\Domain\ValueObject\CustomerReference;
use App\Payment\Domain\ValueObject\MoneyAmount;
use App\Payment\Domain\ValueObject\OrderReference;
use App\Payment\Domain\ValueObject\PaymentId;
use App\Payment\Domain\ValueObject\PaymentStatus;
use Doctrine\ORM\EntityManagerInterface;

it('persists and completes a payment', function () {
    $payment = Payment::createPending(
        PaymentId::fromString('cccccccc-cccc-4ccc-8ccc-cccccccccccc'),
        OrderReference::fromString('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa'),
        CustomerReference::fromString('11111111-1111-4111-8111-111111111111'),
        MoneyAmount::fromCents(3998),
        new DateTimeImmutable('2026-08-20T12:00:00+00:00'),
    );

    $repository = self::getContainer()->get(PaymentRepository::class);
    $entityManager = self::getContainer()->get(EntityManagerInterface::class);

    $repository->save($payment);
    $entityManager->clear();

    $found = $repository->findByOrderId(OrderReference::fromString('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa'));

    expect($found)->not->toBeNull()
        ->and($found->status())->toEqual(PaymentStatus::pending())
        ->and($found->amount()->cents())->toBe(3998);

    $found->complete(new DateTimeImmutable('2026-08-20T13:00:00+00:00'));
    $repository->save($found);
    $entityManager->clear();

    $completed = $repository->findByOrderId(OrderReference::fromString('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa'));

    expect($completed)->not->toBeNull()
        ->and($completed->status())->toEqual(PaymentStatus::completed())
        ->and($completed->completedAt()?->format(DateTimeInterface::ATOM))->toBe('2026-08-20T13:00:00+00:00');
});
