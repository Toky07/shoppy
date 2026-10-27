<?php

declare(strict_types=1);

use App\Invoice\Domain\Entity\Invoice;
use App\Invoice\Domain\ValueObject\CustomerReference;
use App\Invoice\Domain\ValueObject\InvoiceId;
use App\Invoice\Domain\ValueObject\InvoiceLine;
use App\Invoice\Domain\ValueObject\InvoiceNumber;
use App\Invoice\Domain\ValueObject\MoneyAmount;
use App\Invoice\Domain\ValueObject\OrderReference;
use App\Invoice\Domain\ValueObject\PaymentReference;
use App\Invoice\Infrastructure\Notification\InvoiceIssuedMail;
use App\Shared\Application\Event\InvoiceIssued;

function styledInvoiceMail(): InvoiceIssuedMail
{
    return new InvoiceIssuedMail('http://localhost:5173/');
}

function styledInvoiceMailEvent(): InvoiceIssued
{
    return new InvoiceIssued(
        invoiceId: 'ffffffff-ffff-4fff-8fff-ffffffffffff',
        number: 'FAC-2026-0001',
        orderId: 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
        customerId: '11111111-1111-4111-8111-111111111111',
        paymentId: 'dddddddd-dddd-4ddd-8ddd-dddddddddddd',
        amountCents: 4488,
    );
}

function styledInvoiceMailInvoice(): Invoice
{
    return Invoice::issue(
        InvoiceId::fromString('ffffffff-ffff-4fff-8fff-ffffffffffff'),
        InvoiceNumber::fromString('FAC-2026-0001'),
        OrderReference::fromString('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa'),
        CustomerReference::fromString('11111111-1111-4111-8111-111111111111'),
        PaymentReference::fromString('dddddddd-dddd-4ddd-8ddd-dddddddddddd'),
        MoneyAmount::fromCents(4488),
        new DateTimeImmutable('2026-08-20T12:05:00+00:00'),
        [
            InvoiceLine::of('Nuvora Tee', 2, 1999),
            InvoiceLine::of('Livraison Standard', 1, 490),
        ],
    );
}

it('builds a styled html invoice email for the customer', function () {
    $body = styledInvoiceMail()->customerBody(styledInvoiceMailEvent(), styledInvoiceMailInvoice());

    expect(styledInvoiceMail()->customerSubject('FAC-2026-0001')->value())->toBe('Votre facture FAC-2026-0001')
        ->and($body->text())->toBe(implode("\n", [
            'Bonjour,',
            '',
            'Votre facture FAC-2026-0001 est prête. Vous la trouverez en pièce jointe de cet email.',
            '',
            'Nuvora Tee × 2 — 39,98 €',
            'Livraison Standard × 1 — 4,90 €',
            '',
            'Total : 44,88 €',
            '',
            'Merci pour votre confiance.',
            'L’équipe Shoppy',
        ]))
        ->and($body->html())->toContain('Votre facture est prête')
        ->and($body->html())->toContain('FAC-2026-0001')
        ->and($body->html())->toContain('Nuvora Tee')
        ->and($body->html())->toContain('Livraison Standard')
        ->and($body->html())->toContain('44,88 €')
        ->and($body->html())->toContain('#C6EF4C')
        ->and($body->html())->toContain('http://localhost:5173/orders/aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa')
        ->and($body->html())->toContain('Voir ma commande');
});

it('builds a styled html invoice email for the administrator', function () {
    $body = styledInvoiceMail()->adminBody(styledInvoiceMailEvent(), styledInvoiceMailInvoice(), 'ada@shoppy.test');

    expect(styledInvoiceMail()->adminSubject('FAC-2026-0001')->value())->toBe('Nouvelle facture FAC-2026-0001')
        ->and($body->text())->toBe(implode("\n", [
            'Une nouvelle facture a été émise.',
            '',
            'Facture : FAC-2026-0001',
            'Client : ada@shoppy.test',
            'Commande : aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
            'Date : 20/08/2026',
            '',
            'Nuvora Tee × 2 — 39,98 €',
            'Livraison Standard × 1 — 4,90 €',
            '',
            'Total : 44,88 €',
            '',
            'Le PDF est en pièce jointe.',
        ]))
        ->and($body->html())->toContain('Une facture a été émise')
        ->and($body->html())->toContain('ada@shoppy.test')
        ->and($body->html())->toContain('http://localhost:5173/admin/orders/aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa')
        ->and($body->html())->toContain('Ouvrir la commande');
});

it('escapes invoice line descriptions in html', function () {
    $invoice = Invoice::issue(
        InvoiceId::fromString('ffffffff-ffff-4fff-8fff-ffffffffffff'),
        InvoiceNumber::fromString('FAC-2026-0001'),
        OrderReference::fromString('aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa'),
        CustomerReference::fromString('11111111-1111-4111-8111-111111111111'),
        PaymentReference::fromString('dddddddd-dddd-4ddd-8ddd-dddddddddddd'),
        MoneyAmount::fromCents(100),
        new DateTimeImmutable('2026-08-20T12:05:00+00:00'),
        [InvoiceLine::of('<script>alert(1)</script>', 1, 100)],
    );

    $html = styledInvoiceMail()->customerBody(new InvoiceIssued(
        invoiceId: 'ffffffff-ffff-4fff-8fff-ffffffffffff',
        number: 'FAC-2026-0001',
        orderId: 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
        customerId: '11111111-1111-4111-8111-111111111111',
        paymentId: 'dddddddd-dddd-4ddd-8ddd-dddddddddddd',
        amountCents: 100,
    ), $invoice)->html();

    expect($html)->not->toContain('<script>alert(1)</script>')
        ->and($html)->toContain('&lt;script&gt;alert(1)&lt;/script&gt;');
});
