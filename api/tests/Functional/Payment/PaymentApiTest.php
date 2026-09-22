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
        ->and($pending['amount'])->toBe(['cents' => 3998, 'currency' => 'EUR']);

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
    $login = json_decode((string) $this->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);
    $headers = ['HTTP_AUTHORIZATION' => 'Bearer '.$login['accessToken']];

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

it('starts a stripe checkout and leaves the order unpaid', function () {
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
        ->and($checkout['provider'])->toBe('stripe')
        ->and($checkout['status'])->toBe('pending')
        ->and($checkout['completedImmediately'])->toBeFalse()
        ->and($checkout['redirectUrl'])->toStartWith('https://checkout.test/');

    $this->client->jsonRequest('GET', '/payments/by-order/'.$orderId, [], catalogCustomerHeaders());
    $payment = json_decode((string) $this->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);
    expect($payment['status'])->toBe('pending');

    $this->client->jsonRequest('GET', '/orders/'.$orderId, [], catalogCustomerHeaders());
    $orderAfter = json_decode((string) $this->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);
    expect($orderAfter['status'])->toBe('pending');
});

it('confirms a stripe payment from a signed checkout.session.completed webhook', function () {
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
    $sessionId = ltrim((string) parse_url((string) $checkout['redirectUrl'], PHP_URL_PATH), '/');

    $this->client->jsonRequest('POST', '/payments/webhooks/stripe', [
        'type' => 'checkout.session.completed',
        'data' => [
            'object' => [
                'id' => $sessionId,
            ],
        ],
    ], ['HTTP_STRIPE_SIGNATURE' => 'test']);

    expect($this->client->getResponse()->getStatusCode())->toBe(200);

    $this->client->jsonRequest('GET', '/payments/by-order/'.$orderId, [], catalogCustomerHeaders());
    $payment = json_decode((string) $this->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);

    $this->client->jsonRequest('GET', '/orders/'.$orderId, [], catalogCustomerHeaders());
    $orderAfter = json_decode((string) $this->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);

    expect($payment['status'])->toBe('completed')
        ->and($orderAfter['status'])->toBe('paid');
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
