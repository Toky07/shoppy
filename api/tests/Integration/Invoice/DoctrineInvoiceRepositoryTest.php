<?php

declare(strict_types=1);

use App\Invoice\Domain\Entity\Invoice;
use App\Invoice\Domain\Repository\InvoiceRepository;
use App\Invoice\Domain\ValueObject\CustomerReference;
use App\Invoice\Domain\ValueObject\InvoiceId;
use App\Invoice\Domain\ValueObject\InvoiceNumber;
use App\Invoice\Domain\ValueObject\MoneyAmount;
use App\Invoice\Domain\ValueObject\OrderReference;
use App\Invoice\Domain\ValueObject\PaymentReference;
use Doctrine\ORM\EntityManagerInterface;

function persistInvoice(
    InvoiceRepository $invoices,
    string $id,
    string $number,
    string $orderId,
    string $paymentId,
    DateTimeImmutable $issuedAt,
    int $amountCents = 4488,
): void {
    $invoices->save(Invoice::issue(
        InvoiceId::fromString($id),
        InvoiceNumber::fromString($number),
        OrderReference::fromString($orderId),
        CustomerReference::fromString('11111111-1111-4111-8111-111111111111'),
        PaymentReference::fromString($paymentId),
        MoneyAmount::fromCents($amountCents),
        $issuedAt,
    ));
}

it('persists an invoice issued for an order', function () {
    $issuedAt = new DateTimeImmutable('2026-08-20T12:05:00+00:00');
    $repository = self::getContainer()->get(InvoiceRepository::class);
    $entityManager = self::getContainer()->get(EntityManagerInterface::class);

    persistInvoice(
        $repository,
        'ffffffff-ffff-4fff-8fff-ffffffffffff',
        'FAC-2026-0001',
        'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
        'dddddddd-dddd-4ddd-8ddd-dddddddddddd',
        $issuedAt,
    );
    $entityManager->clear();

    $found = $repository->findByOrderId(OrderReference::fromString('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa'));

    expect($found)->not->toBeNull()
        ->and($found->id()->value())->toBe('ffffffff-ffff-4fff-8fff-ffffffffffff')
        ->and($found->number()->value())->toBe('FAC-2026-0001')
        ->and($found->customerId()->value())->toBe('11111111-1111-4111-8111-111111111111')
        ->and($found->paymentId()->value())->toBe('dddddddd-dddd-4ddd-8ddd-dddddddddddd')
        ->and($found->amount()->cents())->toBe(4488)
        ->and($found->issuedAt()->format(DateTimeInterface::ATOM))->toBe('2026-08-20T12:05:00+00:00');
});

it('numbers the next invoice after the highest sequence of the year', function () {
    $repository = self::getContainer()->get(InvoiceRepository::class);

    persistInvoice(
        $repository,
        'ffffffff-ffff-4fff-8fff-ffffffffffff',
        'FAC-2025-0009',
        'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
        'dddddddd-dddd-4ddd-8ddd-dddddddddddd',
        new DateTimeImmutable('2025-12-31T23:00:00+00:00'),
    );
    persistInvoice(
        $repository,
        'eeeeeeee-eeee-4eee-8eee-eeeeeeeeeeee',
        'FAC-2026-0002',
        'bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb',
        'cccccccc-cccc-4ccc-8ccc-cccccccccccc',
        new DateTimeImmutable('2026-01-02T09:00:00+00:00'),
    );

    expect($repository->nextNumber(new DateTimeImmutable('2026-08-20T12:05:00+00:00'))->value())
        ->toBe('FAC-2026-0003');
});
