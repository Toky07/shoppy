<?php

declare(strict_types=1);

use App\Invoice\Domain\Entity\Invoice;
use App\Invoice\Domain\Entity\InvoiceBranding;
use App\Invoice\Domain\Entity\InvoiceOrderSnapshot;
use App\Invoice\Domain\Repository\InvoiceOrderSnapshotRepository;
use App\Invoice\Domain\Repository\InvoiceRepository;
use App\Invoice\Domain\ValueObject\CustomerReference;
use App\Invoice\Domain\ValueObject\InvoiceId;
use App\Invoice\Domain\Repository\InvoiceBrandingRepository;
use App\Invoice\Domain\ValueObject\InvoiceLine;
use App\Invoice\Domain\ValueObject\InvoiceLogo;
use App\Invoice\Domain\ValueObject\InvoiceNumber;
use App\Invoice\Domain\ValueObject\MoneyAmount;
use App\Invoice\Domain\ValueObject\OrderReference;
use App\Invoice\Domain\ValueObject\PaymentReference;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @param list<InvoiceLine> $lines
 */
function persistInvoice(
    InvoiceRepository $invoices,
    string $id,
    string $number,
    string $orderId,
    string $paymentId,
    DateTimeImmutable $issuedAt,
    int $amountCents = 4488,
    array $lines = [],
    ?InvoiceLogo $logo = null,
): void {
    $invoices->save(Invoice::issue(
        InvoiceId::fromString($id),
        InvoiceNumber::fromString($number),
        OrderReference::fromString($orderId),
        CustomerReference::fromString('11111111-1111-4111-8111-111111111111'),
        PaymentReference::fromString($paymentId),
        MoneyAmount::fromCents($amountCents),
        $issuedAt,
        $lines,
        $logo,
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

it('persists billed invoice lines', function () {
    $repository = self::getContainer()->get(InvoiceRepository::class);
    $entityManager = self::getContainer()->get(EntityManagerInterface::class);

    persistInvoice(
        $repository,
        'ffffffff-ffff-4fff-8fff-ffffffffffff',
        'FAC-2026-0001',
        'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
        'dddddddd-dddd-4ddd-8ddd-dddddddddddd',
        new DateTimeImmutable('2026-08-20T12:05:00+00:00'),
        4488,
        [
            InvoiceLine::of('Nuvora Tee', 2, 1999),
            InvoiceLine::of('Livraison Standard', 1, 490),
        ],
    );
    $entityManager->clear();

    $found = $repository->findByOrderId(OrderReference::fromString('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa'));

    expect($found)->not->toBeNull()
        ->and($found->lines())->toHaveCount(2)
        ->and($found->lines()[0]->description())->toBe('Nuvora Tee')
        ->and($found->lines()[0]->quantity())->toBe(2)
        ->and($found->lines()[0]->unitPriceCents())->toBe(1999)
        ->and($found->lines()[1]->description())->toBe('Livraison Standard')
        ->and($found->lines()[1]->unitPriceCents())->toBe(490);
});

it('persists the order snapshot used to fill the invoice', function () {
    $repository = self::getContainer()->get(InvoiceOrderSnapshotRepository::class);
    $entityManager = self::getContainer()->get(EntityManagerInterface::class);
    $orderId = OrderReference::fromString('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa');

    $repository->save(new InvoiceOrderSnapshot(
        $orderId,
        CustomerReference::fromString('11111111-1111-4111-8111-111111111111'),
        [InvoiceLine::of('Nuvora Tee', 2, 1999), InvoiceLine::of('Livraison Standard', 1, 490)],
    ));
    $entityManager->clear();

    $found = $repository->findByOrderId($orderId);

    expect($found)->not->toBeNull()
        ->and($found->customerId()->value())->toBe('11111111-1111-4111-8111-111111111111')
        ->and($found->lines())->toHaveCount(2)
        ->and($found->lines()[0]->description())->toBe('Nuvora Tee')
        ->and($found->lines()[1]->description())->toBe('Livraison Standard');
});

it('persists the snapshotted invoice logo', function () {
    $repository = self::getContainer()->get(InvoiceRepository::class);
    $entityManager = self::getContainer()->get(EntityManagerInterface::class);

    persistInvoice(
        $repository,
        'ffffffff-ffff-4fff-8fff-ffffffffffff',
        'FAC-2026-0001',
        'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
        'dddddddd-dddd-4ddd-8ddd-dddddddddddd',
        new DateTimeImmutable('2026-08-20T12:05:00+00:00'),
        4488,
        [
            InvoiceLine::of('Nuvora Tee', 2, 1999),
            InvoiceLine::of('Livraison Standard', 1, 490),
        ],
        InvoiceLogo::of('invoices/logos/shop.png', 'image/png'),
    );
    $entityManager->clear();

    $found = $repository->findByOrderId(OrderReference::fromString('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa'));

    expect($found?->logo()?->relativePath())->toBe('invoices/logos/shop.png')
        ->and($found?->logo()?->mimeType())->toBe('image/png');
});

it('persists the shop invoice branding', function () {
    $repository = self::getContainer()->get(InvoiceBrandingRepository::class);
    $entityManager = self::getContainer()->get(EntityManagerInterface::class);

    $repository->save(InvoiceBranding::blank()->withLogo(
        InvoiceLogo::of('invoices/logos/shop.png', 'image/png'),
    ));
    $entityManager->clear();

    $found = $repository->get();

    expect($found->logo()?->relativePath())->toBe('invoices/logos/shop.png')
        ->and($found->logo()?->publicUrl())->toBe('/uploads/invoices/logos/shop.png');
});
