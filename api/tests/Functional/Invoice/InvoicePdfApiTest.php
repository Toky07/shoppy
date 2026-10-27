<?php

declare(strict_types=1);

function invoicePdfCatalogProduct(): array
{
    test()->client->jsonRequest('POST', '/products', [
        'name' => 'Nuvora Tee',
        'priceCents' => 1999,
        'stock' => 10,
    ], catalogAdminHeaders());

    return json_decode((string) test()->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);
}

it('downloads the invoice pdf after payment is completed', function () {
    $product = invoicePdfCatalogProduct();
    $order = placeOrderFromCart(catalogCustomerHeaders(), $product['id'], 2);

    $this->client->jsonRequest('POST', '/payments/complete', [
        'orderId' => $order['id'],
    ], catalogCustomerHeaders());
    expect($this->client->getResponse()->getStatusCode())->toBe(200);

    $this->client->request('GET', '/invoices/by-order/'.$order['id'].'/pdf', [], [], catalogCustomerHeaders());
    $response = $this->client->getResponse();

    expect($response->getStatusCode())->toBe(200)
        ->and($response->headers->get('Content-Type'))->toStartWith('application/pdf')
        ->and($response->headers->get('Content-Disposition'))->toContain('FAC-2026-0001.pdf')
        ->and((string) $response->getContent())->toStartWith('%PDF-')
        ->and((string) $response->getContent())->toContain('Nuvora Tee')
        ->and((string) $response->getContent())->toContain('Livraison Standard');
});

it('lets an admin download a customer invoice pdf', function () {
    $product = invoicePdfCatalogProduct();
    $order = placeOrderFromCart(catalogCustomerHeaders(), $product['id'], 2);
    $this->client->jsonRequest('POST', '/payments/complete', [
        'orderId' => $order['id'],
    ], catalogCustomerHeaders());

    $this->client->request('GET', '/invoices/by-order/'.$order['id'].'/pdf', [], [], catalogAdminHeaders());

    expect($this->client->getResponse()->getStatusCode())->toBe(200)
        ->and((string) $this->client->getResponse()->getContent())->toStartWith('%PDF-');
});

it('rejects another customer downloading the invoice pdf', function () {
    $product = invoicePdfCatalogProduct();
    $order = placeOrderFromCart(catalogCustomerHeaders(), $product['id'], 2);
    $this->client->jsonRequest('POST', '/payments/complete', [
        'orderId' => $order['id'],
    ], catalogCustomerHeaders());

    registerCatalogUser($this->client, 'other-invoice@nuvora.test');
    $this->client->jsonRequest('POST', '/auth/login', [
        'email' => 'other-invoice@nuvora.test',
        'password' => 'secret-secret',
    ]);
    $headers = ['HTTP_AUTHORIZATION' => 'Bearer '.sessionAccessToken($this->client)];

    $this->client->request('GET', '/invoices/by-order/'.$order['id'].'/pdf', [], [], $headers);
    $payload = json_decode((string) $this->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);

    expect($this->client->getResponse()->getStatusCode())->toBe(403)
        ->and($payload['error']['code'])->toBe('forbidden');
});

it('rejects unauthenticated invoice pdf access', function () {
    $this->client->request('GET', '/invoices/by-order/aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa/pdf');
    $payload = json_decode((string) $this->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);

    expect($this->client->getResponse()->getStatusCode())->toBe(401)
        ->and($payload['error']['code'])->toBe('unauthenticated');
});

it('rejects downloading a pdf before the invoice exists', function () {
    $product = invoicePdfCatalogProduct();
    $order = placeOrderFromCart(catalogCustomerHeaders(), $product['id'], 2);

    $this->client->request('GET', '/invoices/by-order/'.$order['id'].'/pdf', [], [], catalogCustomerHeaders());
    $payload = json_decode((string) $this->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);

    expect($this->client->getResponse()->getStatusCode())->toBe(404)
        ->and($payload['error']['code'])->toBe('invoice_not_found');
});
