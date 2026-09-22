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

    $this->client->jsonRequest('POST', '/orders', [
        ...deliveryFields(),
        'items' => [
            [
                'productId' => $product['id'],
                'quantity' => 2,
            ],
        ],
    ], catalogCustomerHeaders());

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

it('ignores a client-supplied customer id and prices', function () {
    $product = createOrderCatalogProduct(priceCents: 1999);
    $customerId = authenticatedOrderCustomerId();

    $this->client->jsonRequest('POST', '/orders', [
        ...deliveryFields(),
        'customerId' => '11111111-1111-4111-8111-111111111111',
        'items' => [
            [
                'productId' => $product['id'],
                'quantity' => 1,
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
        ->and($payload['items'][0]['unitPrice']['cents'])->toBe(1999);
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

it('rejects a missing items field', function () {
    $this->client->jsonRequest('POST', '/orders', [], catalogCustomerHeaders());

    $response = $this->client->getResponse();
    $payload = json_decode((string) $response->getContent(), true, flags: JSON_THROW_ON_ERROR);

    expect($response->getStatusCode())->toBe(400)
        ->and($payload['error']['code'])->toBe('validation_error')
        ->and($payload['error']['message'])->toBe('The request is invalid.')
        ->and($payload['error']['violations'])->toBe([
            ['field' => 'items', 'message' => 'This field is required.'],
        ]);
});

it('rejects an empty items list', function () {
    $this->client->jsonRequest('POST', '/orders', [
        ...deliveryFields(),
        'items' => [],
    ], catalogCustomerHeaders());

    $response = $this->client->getResponse();
    $payload = json_decode((string) $response->getContent(), true, flags: JSON_THROW_ON_ERROR);

    expect($response->getStatusCode())->toBe(400)
        ->and($payload['error']['code'])->toBe('validation_error')
        ->and($payload['error']['violations'])->toBe([
            ['field' => 'items', 'message' => 'An order must contain at least one item.'],
        ]);
});

it('rejects an unknown shipping method without decrementing stock', function () {
    $this->client->jsonRequest('POST', '/products', [
        'name' => 'Nuvora Tee',
        'priceCents' => 1999,
        'stock' => 4,
    ], catalogAdminHeaders());
    $product = json_decode((string) $this->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);

    $this->client->jsonRequest('POST', '/orders', [
        ...deliveryFields(),
        'shippingMethod' => 'drone',
        'items' => [[
            'productId' => $product['id'],
            'quantity' => 1,
        ]],
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

it('rejects an unknown catalog product', function () {
    $this->client->jsonRequest('POST', '/orders', [
        ...deliveryFields(),
        'items' => [
            [
                'productId' => '550e8400-e29b-41d4-a716-446655440000',
                'quantity' => 1,
            ],
        ],
    ], catalogCustomerHeaders());

    $response = $this->client->getResponse();
    $payload = json_decode((string) $response->getContent(), true, flags: JSON_THROW_ON_ERROR);

    expect($response->getStatusCode())->toBe(404)
        ->and($payload['error'])->toBe([
            'code' => 'product_not_found',
            'message' => 'The requested resource was not found.',
        ])
        ->and((string) $response->getContent())->not->toContain('550e8400');
});

it('rejects a quantity below one', function () {
    $product = createOrderCatalogProduct();

    $this->client->jsonRequest('POST', '/orders', [
        ...deliveryFields(),
        'items' => [
            [
                'productId' => $product['id'],
                'quantity' => 0,
            ],
        ],
    ], catalogCustomerHeaders());

    $response = $this->client->getResponse();
    $payload = json_decode((string) $response->getContent(), true, flags: JSON_THROW_ON_ERROR);

    expect($response->getStatusCode())->toBe(400)
        ->and($payload['error']['code'])->toBe('validation_error')
        ->and($payload['error']['violations'])->toBe([
            ['field' => 'quantity', 'message' => 'Quantity must be at least 1.'],
        ]);
});

it('rejects an order when stock is insufficient', function () {
    $this->client->jsonRequest('POST', '/products', [
        'name' => 'Limited Tee',
        'priceCents' => 1999,
        'stock' => 1,
    ], catalogAdminHeaders());
    $product = json_decode((string) $this->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);

    $this->client->jsonRequest('POST', '/orders', [
        ...deliveryFields(),
        'items' => [
            [
                'productId' => $product['id'],
                'quantity' => 2,
            ],
        ],
    ], catalogCustomerHeaders());

    $response = $this->client->getResponse();
    $payload = json_decode((string) $response->getContent(), true, flags: JSON_THROW_ON_ERROR);

    expect($response->getStatusCode())->toBe(409)
        ->and($payload['error']['code'])->toBe('insufficient_product_stock');

    $this->client->jsonRequest('GET', '/products/'.$product['id']);
    $fetched = json_decode((string) $this->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);

    expect($fetched['stock'])->toBe(1);
});

it('does not decrement the first product when a later line has no stock', function () {
    $this->client->jsonRequest('POST', '/products', [
        'name' => 'Available Tee',
        'priceCents' => 1999,
        'stock' => 4,
    ], catalogAdminHeaders());
    $available = json_decode((string) $this->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);

    $this->client->jsonRequest('POST', '/products', [
        'name' => 'Empty Tee',
        'priceCents' => 1999,
        'stock' => 0,
    ], catalogAdminHeaders());
    $empty = json_decode((string) $this->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);

    $this->client->jsonRequest('POST', '/orders', [
        ...deliveryFields(),
        'items' => [
            ['productId' => $available['id'], 'quantity' => 1],
            ['productId' => $empty['id'], 'quantity' => 1],
        ],
    ], catalogCustomerHeaders());

    $response = $this->client->getResponse();
    $payload = json_decode((string) $response->getContent(), true, flags: JSON_THROW_ON_ERROR);

    expect($response->getStatusCode())->toBe(409)
        ->and($payload['error']['code'])->toBe('insufficient_product_stock');

    $this->client->jsonRequest('GET', '/products/'.$available['id']);
    $fetched = json_decode((string) $this->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);
    expect($fetched['stock'])->toBe(4);
});

it('decrements stock when an order is placed and restores it on cancel', function () {
    $this->client->jsonRequest('POST', '/products', [
        'name' => 'Stock Tee',
        'priceCents' => 1999,
        'stock' => 5,
    ], catalogAdminHeaders());
    $product = json_decode((string) $this->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);

    $this->client->jsonRequest('POST', '/orders', [
        ...deliveryFields(),
        'items' => [
            [
                'productId' => $product['id'],
                'quantity' => 2,
            ],
        ],
    ], catalogCustomerHeaders());
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
