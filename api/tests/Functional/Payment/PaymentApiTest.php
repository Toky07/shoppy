<?php

declare(strict_types=1);

function paymentCatalogProduct(int $stock = 10): array
{
    test()->client->jsonRequest('POST', '/products', [
        'name' => 'Nuvora Tee',
        'priceCents' => 1999,
        'stock' => $stock,
    ], catalogAdminHeaders());

    return json_decode((string) test()->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);
}

it('creates a pending payment when an order is placed and completes it via events', function () {
    $product = paymentCatalogProduct();

    $this->client->jsonRequest('POST', '/orders', [
        ...deliveryFields(),
        'items' => [[
            'productId' => $product['id'],
            'quantity' => 2,
        ]],
    ], catalogCustomerHeaders());

    $order = json_decode((string) $this->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);
    expect($this->client->getResponse()->getStatusCode())->toBe(201)
        ->and($order['status'])->toBe('pending');

    $this->client->jsonRequest('GET', '/payments/by-order/'.$order['id'], [], catalogCustomerHeaders());
    $pending = json_decode((string) $this->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);

    expect($this->client->getResponse()->getStatusCode())->toBe(200)
        ->and($pending['status'])->toBe('pending')
        ->and($pending['orderId'])->toBe($order['id'])
        ->and($pending['amount'])->toBe(['cents' => 4488, 'currency' => 'EUR']);

    $this->client->jsonRequest('POST', '/payments/complete', [
        'orderId' => $order['id'],
    ], catalogCustomerHeaders());

    $paidPayment = json_decode((string) $this->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);

    expect($this->client->getResponse()->getStatusCode())->toBe(200)
        ->and($paidPayment['status'])->toBe('completed')
        ->and($paidPayment['completedAt'])->not->toBeNull();

    $this->client->jsonRequest('GET', '/orders/'.$order['id'], [], catalogCustomerHeaders());
    $paidOrder = json_decode((string) $this->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);

    expect($paidOrder['status'])->toBe('paid');
});

it('rejects paying another customer order payment', function () {
    $product = paymentCatalogProduct();

    $this->client->jsonRequest('POST', '/orders', [
        ...deliveryFields(),
        'items' => [[
            'productId' => $product['id'],
            'quantity' => 1,
        ]],
    ], catalogCustomerHeaders());
    $order = json_decode((string) $this->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);

    $intruder = registerCatalogUser($this->client, 'pay-intruder@nuvora.test');
    $this->client->jsonRequest('POST', '/auth/login', [
        'email' => 'pay-intruder@nuvora.test',
        'password' => 'secret-secret',
    ]);
    $headers = ['HTTP_AUTHORIZATION' => 'Bearer '.sessionAccessToken($this->client)];

    $this->client->jsonRequest('POST', '/payments/complete', [
        'orderId' => $order['id'],
    ], $headers);

    $response = $this->client->getResponse();
    $payload = json_decode((string) $response->getContent(), true, flags: JSON_THROW_ON_ERROR);

    expect($response->getStatusCode())->toBe(403)
        ->and($payload['error']['code'])->toBe('forbidden')
        ->and($intruder['id'])->not->toBe($order['customerId']);
});

it('rejects unauthenticated payment completion', function () {
    $this->client->jsonRequest('POST', '/payments/complete', [
        'orderId' => 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
    ]);

    $response = $this->client->getResponse();
    $payload = json_decode((string) $response->getContent(), true, flags: JSON_THROW_ON_ERROR);

    expect($response->getStatusCode())->toBe(401)
        ->and($payload['error']['code'])->toBe('unauthenticated');
});

it('cancels the pending payment when the order is cancelled', function () {
    $product = paymentCatalogProduct();

    $this->client->jsonRequest('POST', '/orders', [
        ...deliveryFields(),
        'items' => [[
            'productId' => $product['id'],
            'quantity' => 1,
        ]],
    ], catalogCustomerHeaders());
    $order = json_decode((string) $this->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);

    $this->client->jsonRequest('POST', '/orders/'.$order['id'].'/cancel', [], catalogCustomerHeaders());
    expect($this->client->getResponse()->getStatusCode())->toBe(200);

    $this->client->jsonRequest('GET', '/payments/by-order/'.$order['id'], [], catalogCustomerHeaders());
    $payment = json_decode((string) $this->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);

    expect($payment['status'])->toBe('cancelled');

    $this->client->jsonRequest('POST', '/payments/complete', [
        'orderId' => $order['id'],
    ], catalogCustomerHeaders());

    $response = $this->client->getResponse();
    $payload = json_decode((string) $response->getContent(), true, flags: JSON_THROW_ON_ERROR);

    expect($response->getStatusCode())->toBe(409)
        ->and($payload['error']['code'])->toBe('payment_not_payable');
});

it('ignores a client-selected provider and checks out with the server provider', function () {
    $product = paymentCatalogProduct();

    $this->client->jsonRequest('POST', '/orders', [
        ...deliveryFields(),
        'items' => [[
            'productId' => $product['id'],
            'quantity' => 2,
        ]],
    ], catalogCustomerHeaders());
    $order = json_decode((string) $this->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);
    $orderId = $order['id'];

    $this->client->jsonRequest('POST', '/payments/checkout', [
        'orderId' => $orderId,
        'provider' => 'stripe',
        'successUrl' => 'http://localhost:5173/orders/'.$orderId.'?payment=success',
        'cancelUrl' => 'http://localhost:5173/orders/'.$orderId.'?payment=cancel',
    ], catalogCustomerHeaders());

    $checkout = json_decode((string) $this->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);

    expect($this->client->getResponse()->getStatusCode())->toBe(200)
        ->and($checkout['provider'])->toBe('local')
        ->and($checkout['status'])->toBe('pending')
        ->and($checkout['completedImmediately'])->toBeTrue()
        ->and($checkout['redirectUrl'])->toBeNull();

    $this->client->jsonRequest('GET', '/payments/by-order/'.$orderId, [], catalogCustomerHeaders());
    $payment = json_decode((string) $this->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);
    expect($payment['status'])->toBe('pending');

    $this->client->jsonRequest('GET', '/orders/'.$orderId, [], catalogCustomerHeaders());
    $orderAfter = json_decode((string) $this->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);
    expect($orderAfter['status'])->toBe('pending');
});

it('rejects a checkout url outside the allowed origins', function () {
    $product = paymentCatalogProduct();

    $this->client->jsonRequest('POST', '/orders', [
        ...deliveryFields(),
        'items' => [[
            'productId' => $product['id'],
            'quantity' => 1,
        ]],
    ], catalogCustomerHeaders());
    $order = json_decode((string) $this->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);

    $this->client->jsonRequest('POST', '/payments/checkout', [
        'orderId' => $order['id'],
        'successUrl' => 'https://evil.test/orders/'.$order['id'],
        'cancelUrl' => 'http://localhost:5173/orders/'.$order['id'].'?payment=cancel',
    ], catalogCustomerHeaders());

    $response = $this->client->getResponse();
    $payload = json_decode((string) $response->getContent(), true, flags: JSON_THROW_ON_ERROR);

    expect($response->getStatusCode())->toBe(400)
        ->and($payload['error']['code'])->toBe('validation_error')
        ->and($payload['error']['violations'][0]['field'])->toBe('successUrl');
});

it('rejects an unsigned stripe webhook', function () {
    $this->client->jsonRequest('POST', '/payments/webhooks/stripe', [
        'type' => 'checkout.session.completed',
        'data' => [
            'object' => [
                'id' => 'cs_test_unsigned',
            ],
        ],
    ]);

    $response = $this->client->getResponse();
    $payload = json_decode((string) $response->getContent(), true, flags: JSON_THROW_ON_ERROR);

    expect($response->getStatusCode())->toBe(400)
        ->and($payload['error']['code'])->toBe('validation_error');
});

it('refuses a stripe checkout once the order is marked paid', function () {
    $product = paymentCatalogProduct();

    $this->client->jsonRequest('POST', '/orders', [
        ...deliveryFields(),
        'items' => [[
            'productId' => $product['id'],
            'quantity' => 1,
        ]],
    ], catalogCustomerHeaders());
    $order = json_decode((string) $this->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);

    $this->client->jsonRequest('POST', '/orders/'.$order['id'].'/mark-paid', [], catalogAdminHeaders());
    expect($this->client->getResponse()->getStatusCode())->toBe(200);

    $this->client->jsonRequest('GET', '/payments/by-order/'.$order['id'], [], catalogCustomerHeaders());
    $payment = json_decode((string) $this->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);
    expect($payment['status'])->toBe('completed');

    $this->client->jsonRequest('POST', '/payments/checkout', [
        'orderId' => $order['id'],
        'provider' => 'stripe',
        'successUrl' => 'http://localhost:5173/orders/'.$order['id'].'?payment=success',
        'cancelUrl' => 'http://localhost:5173/orders/'.$order['id'].'?payment=cancel',
    ], catalogCustomerHeaders());

    $response = $this->client->getResponse();
    $payload = json_decode((string) $response->getContent(), true, flags: JSON_THROW_ON_ERROR);

    expect($response->getStatusCode())->toBe(409)
        ->and($payload['error']['code'])->toBe('payment_not_payable');
});

it('ignores a stripe webhook after the order is cancelled', function () {
    $product = paymentCatalogProduct();

    $this->client->jsonRequest('POST', '/orders', [
        ...deliveryFields(),
        'items' => [[
            'productId' => $product['id'],
            'quantity' => 1,
        ]],
    ], catalogCustomerHeaders());
    $order = json_decode((string) $this->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);
    $orderId = $order['id'];

    $this->client->jsonRequest('POST', '/payments/checkout', [
        'orderId' => $orderId,
        'successUrl' => 'http://localhost:5173/orders/'.$orderId.'?payment=success',
        'cancelUrl' => 'http://localhost:5173/orders/'.$orderId.'?payment=cancel',
    ], catalogCustomerHeaders());
    expect($this->client->getResponse()->getStatusCode())->toBe(200);

    $this->client->jsonRequest('POST', '/orders/'.$orderId.'/cancel', [], catalogCustomerHeaders());
    expect($this->client->getResponse()->getStatusCode())->toBe(200);

    $this->client->jsonRequest('POST', '/payments/webhooks/stripe', [
        'type' => 'checkout.session.completed',
        'data' => [
            'object' => [
                'id' => 'cs_test_late',
                'amount_total' => 2489,
            ],
        ],
    ], ['HTTP_STRIPE_SIGNATURE' => 'test']);
    expect($this->client->getResponse()->getStatusCode())->toBe(200);

    $this->client->jsonRequest('GET', '/orders/'.$orderId, [], catalogCustomerHeaders());
    $orderAfter = json_decode((string) $this->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);
    expect($orderAfter['status'])->toBe('cancelled');
});
