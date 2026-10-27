<?php

declare(strict_types=1);

it('lets a guest place an order with an email and cart lines', function () {
    $product = cartProduct(stock: 5);

    $this->client->jsonRequest('POST', '/cart/checkout', [
        'email' => 'guest@nuvora.test',
        'items' => [
            ['productId' => $product['id'], 'quantity' => 2],
        ],
        ...deliveryFields(),
    ]);

    $response = $this->client->getResponse();
    $order = json_decode((string) $response->getContent(), true, flags: JSON_THROW_ON_ERROR);

    expect($response->getStatusCode())->toBe(201)
        ->and($order['status'])->toBe('pending')
        ->and($order['items'][0]['quantity'])->toBe(2)
        ->and($order['user']['email'])->toBe('guest@nuvora.test')
        ->and($order['user']['emailVerified'])->toBeFalse()
        ->and($this->client->getCookieJar()->get('shoppy_session')?->getValue())->not->toBe('');

    $this->client->jsonRequest('GET', '/orders/'.$order['id']);
    expect($this->client->getResponse()->getStatusCode())->toBe(200);

    $this->client->jsonRequest('GET', '/products/'.$product['id']);
    $fetched = json_decode((string) $this->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);
    expect($fetched['stock'])->toBe(3);
});

it('asks an existing customer to sign in instead of checking out as a guest', function () {
    catalogCustomerHeaders();
    $product = cartProduct();

    $this->client->jsonRequest('POST', '/cart/checkout', [
        'email' => 'customer@nuvora.test',
        'items' => [
            ['productId' => $product['id'], 'quantity' => 1],
        ],
        ...deliveryFields(),
    ]);

    $payload = json_decode((string) $this->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);

    expect($this->client->getResponse()->getStatusCode())->toBe(409)
        ->and($payload['error']['code'])->toBe('account_required');
});

it('rejects a guest checkout without an email or items', function () {
    $this->client->jsonRequest('POST', '/cart/checkout', deliveryFields());

    $payload = json_decode((string) $this->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);

    expect($this->client->getResponse()->getStatusCode())->toBe(400)
        ->and($payload['error']['code'])->toBe('validation_error');
});
