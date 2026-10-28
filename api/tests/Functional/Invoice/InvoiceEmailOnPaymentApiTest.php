<?php

declare(strict_types=1);

use App\Tests\Doubles\InMemoryMailer;

function invoiceEmailCatalogProduct(): array
{
    test()->client->jsonRequest('POST', '/products', [
        'name' => 'Nuvora Tee',
        'priceCents' => 1999,
        'stock' => 10,
    ], catalogAdminHeaders());

    return json_decode((string) test()->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);
}

it('emails the styled invoice when the payment is confirmed', function () {
    $product = invoiceEmailCatalogProduct();
    $order = placeOrderFromCart(catalogCustomerHeaders(), $product['id'], 2);
    InMemoryMailer::resetMailbox();

    $this->client->jsonRequest('POST', '/payments/complete', [
        'orderId' => $order['id'],
    ], catalogCustomerHeaders());
    expect($this->client->getResponse()->getStatusCode())->toBe(200);

    $byRecipient = [];
    foreach (InMemoryMailer::mailbox() as $email) {
        $byRecipient[$email->to()->value()][] = $email;
    }

    expect($byRecipient)->toHaveKey('customer@nuvora.test')
        ->and($byRecipient)->toHaveKey('admin@nuvora.test');

    $customerInvoice = null;
    foreach ($byRecipient['customer@nuvora.test'] as $email) {
        if (str_starts_with($email->subject()->value(), 'Votre facture ')) {
            $customerInvoice = $email;
            break;
        }
    }

    $adminInvoice = $byRecipient['admin@nuvora.test'][0];

    expect($customerInvoice)->not->toBeNull()
        ->and($customerInvoice->body()->html())->toContain('Votre facture est prête')
        ->and($customerInvoice->attachments())->toHaveCount(1)
        ->and($customerInvoice->attachments()[0]->mimeType())->toBe('application/pdf')
        ->and($adminInvoice->subject()->value())->toStartWith('Nouvelle facture ')
        ->and($adminInvoice->body()->html())->toContain('Une facture a été émise')
        ->and($adminInvoice->attachments())->toHaveCount(1);
});

it('emails the styled invoice when an admin marks the order paid', function () {
    $product = invoiceEmailCatalogProduct();
    $order = placeOrderFromCart(catalogCustomerHeaders(), $product['id'], 2);
    InMemoryMailer::resetMailbox();

    $this->client->jsonRequest('POST', '/orders/'.$order['id'].'/mark-paid', [], catalogAdminHeaders());
    expect($this->client->getResponse()->getStatusCode())->toBe(200);

    $subjects = array_map(
        static fn ($email): string => $email->subject()->value(),
        InMemoryMailer::mailbox(),
    );

    expect($subjects)->toContain('Votre facture FAC-2026-0001')
        ->and($subjects)->toContain('Nouvelle facture FAC-2026-0001');
});
