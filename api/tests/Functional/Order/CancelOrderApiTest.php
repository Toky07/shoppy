<?php

declare(strict_types=1);

function cancelLifecycleProduct(string $name = 'Nuvora Tee', int $priceCents = 1999): array
{
    test()->client->jsonRequest('POST', '/products', [
        'name' => $name,
        'priceCents' => $priceCents,
        'stock' => 100,
    ], catalogAdminHeaders());

    return json_decode((string) test()->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);
}

function cancelLifecycleLogin(string $email): array
{
    $user = registerCatalogUser(test()->client, $email);

    test()->client->jsonRequest('POST', '/auth/login', [
        'email' => $email,
        'password' => 'secret-secret',
    ]);

    return [
        'user' => $user,
        'headers' => ['HTTP_AUTHORIZATION' => 'Bearer '.sessionAccessToken(test()->client)],
    ];
}

function cancelLifecyclePlaceOrder(array $headers, string $productId): array
{
    test()->client->jsonRequest('POST', '/orders', [
        ...deliveryFields(),
        'items' => [[
            'productId' => $productId,
            'quantity' => 1,
        ]],
    ], $headers);

    return json_decode((string) test()->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);
}

it('cancels a pending order for its owner', function () {
    $product = cancelLifecycleProduct();
    $owner = cancelLifecycleLogin('cancel-owner@nuvora.test');
    $order = cancelLifecyclePlaceOrder($owner['headers'], $product['id']);

    $this->client->jsonRequest('POST', '/orders/'.$order['id'].'/cancel', [], $owner['headers']);

    $response = $this->client->getResponse();
    $payload = json_decode((string) $response->getContent(), true, flags: JSON_THROW_ON_ERROR);

    expect($response->getStatusCode())->toBe(200)
        ->and($payload['id'])->toBe($order['id'])
        ->and($payload['status'])->toBe('cancelled');
});

it('rejects cancelling another customer order', function () {
    $product = cancelLifecycleProduct();
    $owner = cancelLifecycleLogin('cancel-owner@nuvora.test');
    $intruder = cancelLifecycleLogin('cancel-intruder@nuvora.test');
    $order = cancelLifecyclePlaceOrder($owner['headers'], $product['id']);

    $this->client->jsonRequest('POST', '/orders/'.$order['id'].'/cancel', [], $intruder['headers']);

    $response = $this->client->getResponse();
    $payload = json_decode((string) $response->getContent(), true, flags: JSON_THROW_ON_ERROR);

    expect($response->getStatusCode())->toBe(403)
        ->and($payload['error']['code'])->toBe('forbidden');
});

it('rejects unauthenticated cancellation', function () {
    $this->client->jsonRequest('POST', '/orders/aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa/cancel');

    $response = $this->client->getResponse();
    $payload = json_decode((string) $response->getContent(), true, flags: JSON_THROW_ON_ERROR);

    expect($response->getStatusCode())->toBe(401)
        ->and($payload['error']['code'])->toBe('unauthenticated');
});

it('rejects cancelling a paid order', function () {
    $product = cancelLifecycleProduct();
    $owner = cancelLifecycleLogin('cancel-owner@nuvora.test');
    $order = cancelLifecyclePlaceOrder($owner['headers'], $product['id']);

    $this->client->jsonRequest('POST', '/orders/'.$order['id'].'/mark-paid', [], catalogAdminHeaders());
    expect($this->client->getResponse()->getStatusCode())->toBe(200);

    $this->client->jsonRequest('POST', '/orders/'.$order['id'].'/cancel', [], $owner['headers']);

    $response = $this->client->getResponse();
    $payload = json_decode((string) $response->getContent(), true, flags: JSON_THROW_ON_ERROR);

    expect($response->getStatusCode())->toBe(409)
        ->and($payload['error']['code'])->toBe('invalid_order_transition');
});
