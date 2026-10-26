<?php

declare(strict_types=1);

function createOrderCatalogProduct(string $name = 'Nuvora Tee', int $priceCents = 1999): array
{
    test()->client->jsonRequest('POST', '/products', [
        'name' => $name,
        'priceCents' => $priceCents,
        'stock' => 100,
    ], catalogAdminHeaders());

    return json_decode((string) test()->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);
}

function authenticatedOrderCustomerId(): string
{
    test()->client->jsonRequest('GET', '/auth/me', [], catalogCustomerHeaders());

    $me = json_decode((string) test()->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);

    return $me['id'];
}

it('places an order as the authenticated customer with catalog snapshots', function () {
    $product = createOrderCatalogProduct();
    $customerId = authenticatedOrderCustomerId();
    addProductToCustomerCart(catalogCustomerHeaders(), $product['id'], 2);

    $this->client->jsonRequest('POST', '/orders', deliveryFields(), catalogCustomerHeaders());

    $response = $this->client->getResponse();
    $payload = json_decode((string) $response->getContent(), true, flags: JSON_THROW_ON_ERROR);

    expect($response->getStatusCode())->toBe(201)
        ->and($payload['customerId'])->toBe($customerId)
        ->and($payload['status'])->toBe('pending')
        ->and($payload['items'])->toBe([
            [
                'productId' => $product['id'],
                'name' => 'Nuvora Tee',
                'quantity' => 2,
                'unitPrice' => [
                    'cents' => 1999,
                    'currency' => 'EUR',
                ],
                'lineTotal' => [
                    'cents' => 3998,
                    'currency' => 'EUR',
                ],
            ],
        ])
        ->and($payload['shipping'])->toBe([
            'method' => 'standard',
            'label' => 'Standard',
            'fee' => [
                'cents' => 490,
                'currency' => 'EUR',
            ],
        ])
        ->and($payload['total'])->toBe([
            'cents' => 4488,
            'currency' => 'EUR',
        ])
        ->and($response->headers->get('Location'))->toBe('/orders/'.$payload['id']);
});

it('ignores a client-supplied customer id, prices and items', function () {
    $product = createOrderCatalogProduct(priceCents: 1999);
    $customerId = authenticatedOrderCustomerId();
    addProductToCustomerCart(catalogCustomerHeaders(), $product['id'], 1);

    $this->client->jsonRequest('POST', '/orders', [
        ...deliveryFields(),
        'customerId' => '11111111-1111-4111-8111-111111111111',
        'items' => [
            [
                'productId' => $product['id'],
                'quantity' => 99,
                'name' => 'Hijacked Tee',
                'priceCents' => 1,
            ],
        ],
    ], catalogCustomerHeaders());

    $response = $this->client->getResponse();
    $payload = json_decode((string) $response->getContent(), true, flags: JSON_THROW_ON_ERROR);

    expect($response->getStatusCode())->toBe(201)
        ->and($payload['customerId'])->toBe($customerId)
        ->and($payload['customerId'])->not->toBe('11111111-1111-4111-8111-111111111111')
        ->and($payload['items'][0]['name'])->toBe('Nuvora Tee')
        ->and($payload['items'][0]['unitPrice']['cents'])->toBe(1999)
        ->and($payload['items'][0]['quantity'])->toBe(1);
});

it('rejects unauthenticated order placement', function () {
    $this->client->jsonRequest('POST', '/orders', [
        ...deliveryFields(),
        'items' => [
            [
                'productId' => '550e8400-e29b-41d4-a716-446655440000',
                'quantity' => 1,
            ],
        ],
    ]);

    $response = $this->client->getResponse();
    $payload = json_decode((string) $response->getContent(), true, flags: JSON_THROW_ON_ERROR);

    expect($response->getStatusCode())->toBe(401)
        ->and($payload['error'])->toBe([
            'code' => 'unauthenticated',
            'message' => 'The request is not authenticated.',
        ]);
});

it('rejects placing an order from an empty cart', function () {
    $this->client->jsonRequest('POST', '/orders', deliveryFields(), catalogCustomerHeaders());

    $response = $this->client->getResponse();
    $payload = json_decode((string) $response->getContent(), true, flags: JSON_THROW_ON_ERROR);

    expect($response->getStatusCode())->toBe(400)
        ->and($payload['error']['code'])->toBe('validation_error')
        ->and($payload['error']['violations'])->toBe([
            ['field' => 'items', 'message' => 'Cart cannot be empty.'],
        ]);
});

it('refuses a second direct order after the cart has been claimed', function () {
    $product = createOrderCatalogProduct();
    addProductToCustomerCart(catalogCustomerHeaders(), $product['id'], 1);

    $this->client->jsonRequest('POST', '/orders', deliveryFields(), catalogCustomerHeaders());
    expect($this->client->getResponse()->getStatusCode())->toBe(201);

    $this->client->jsonRequest('POST', '/orders', deliveryFields(), catalogCustomerHeaders());
    $payload = json_decode((string) $this->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);

    expect($this->client->getResponse()->getStatusCode())->toBe(400)
        ->and($payload['error']['violations'][0]['message'])->toBe('Cart cannot be empty.');
});

it('rejects an unknown shipping method without decrementing stock', function () {
    $this->client->jsonRequest('POST', '/products', [
        'name' => 'Nuvora Tee',
        'priceCents' => 1999,
        'stock' => 4,
    ], catalogAdminHeaders());
    $product = json_decode((string) $this->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);
    addProductToCustomerCart(catalogCustomerHeaders(), $product['id'], 1);

    $this->client->jsonRequest('POST', '/orders', [
        ...deliveryFields(),
        'shippingMethod' => 'drone',
    ], catalogCustomerHeaders());

    $response = $this->client->getResponse();
    $payload = json_decode((string) $response->getContent(), true, flags: JSON_THROW_ON_ERROR);

    expect($response->getStatusCode())->toBe(400)
        ->and($payload['error']['code'])->toBe('validation_error')
        ->and($payload['error']['violations'])->toBe([
            ['field' => 'shippingMethod', 'message' => 'Shipping method is not available.'],
        ]);

    $this->client->jsonRequest('GET', '/products/'.$product['id']);
    $fetched = json_decode((string) $this->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);
    expect($fetched['stock'])->toBe(4);
});

it('rejects an order when stock is insufficient', function () {
    $this->client->jsonRequest('POST', '/products', [
        'name' => 'Limited Tee',
        'priceCents' => 1999,
        'stock' => 1,
    ], catalogAdminHeaders());
    $product = json_decode((string) $this->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);
    addProductToCustomerCart(catalogCustomerHeaders(), $product['id'], 1);
    $this->client->jsonRequest('PUT', '/products/'.$product['id'].'/stock', [
        'stock' => 0,
    ], catalogAdminHeaders());

    $this->client->jsonRequest('POST', '/orders', deliveryFields(), catalogCustomerHeaders());

    $response = $this->client->getResponse();
    $payload = json_decode((string) $response->getContent(), true, flags: JSON_THROW_ON_ERROR);

    expect($response->getStatusCode())->toBe(409)
        ->and($payload['error']['code'])->toBe('insufficient_product_stock');

    $this->client->jsonRequest('GET', '/products/'.$product['id']);
    $fetched = json_decode((string) $this->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);

    expect($fetched['stock'])->toBe(0);
});

it('decrements stock when an order is placed and restores it on cancel', function () {
    $this->client->jsonRequest('POST', '/products', [
        'name' => 'Stock Tee',
        'priceCents' => 1999,
        'stock' => 5,
    ], catalogAdminHeaders());
    $product = json_decode((string) $this->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);
    addProductToCustomerCart(catalogCustomerHeaders(), $product['id'], 2);

    $this->client->jsonRequest('POST', '/orders', deliveryFields(), catalogCustomerHeaders());
    $order = json_decode((string) $this->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);

    $this->client->jsonRequest('GET', '/products/'.$product['id']);
    $afterPlace = json_decode((string) $this->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);
    expect($afterPlace['stock'])->toBe(3);

    $this->client->jsonRequest('POST', '/orders/'.$order['id'].'/cancel', [], catalogCustomerHeaders());
    expect($this->client->getResponse()->getStatusCode())->toBe(200);

    $this->client->jsonRequest('GET', '/products/'.$product['id']);
    $afterCancel = json_decode((string) $this->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);
    expect($afterCancel['stock'])->toBe(5);
});
