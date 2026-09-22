<?php

declare(strict_types=1);

function markPaidLifecycleProduct(string $name = 'Nuvora Tee', int $priceCents = 1999): array
{
    test()->client->jsonRequest('POST', '/products', [
        'name' => $name,
        'priceCents' => $priceCents,
        'stock' => 100,
    ], catalogAdminHeaders());

    return json_decode((string) test()->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);
}

function markPaidLifecycleLogin(string $email): array
{
    $user = registerCatalogUser(test()->client, $email);

    test()->client->jsonRequest('POST', '/auth/login', [
        'email' => $email,
        'password' => 'secret-secret',
    ]);

    $login = json_decode((string) test()->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);

    return [
        'user' => $user,
        'headers' => ['HTTP_AUTHORIZATION' => 'Bearer '.$login['accessToken']],
    ];
}

function markPaidLifecyclePlaceOrder(array $headers, string $productId): array
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

it('marks a pending order as paid for an admin', function () {
    $product = markPaidLifecycleProduct();
    $owner = markPaidLifecycleLogin('paid-owner@nuvora.test');
    $order = markPaidLifecyclePlaceOrder($owner['headers'], $product['id']);

    $this->client->jsonRequest('POST', '/orders/'.$order['id'].'/mark-paid', [], catalogAdminHeaders());

    $response = $this->client->getResponse();
    $payload = json_decode((string) $response->getContent(), true, flags: JSON_THROW_ON_ERROR);

    expect($response->getStatusCode())->toBe(200)
        ->and($payload['id'])->toBe($order['id'])
        ->and($payload['status'])->toBe('paid');
});

it('rejects a customer marking an order as paid', function () {
    $product = markPaidLifecycleProduct();
    $owner = markPaidLifecycleLogin('paid-owner@nuvora.test');
    $order = markPaidLifecyclePlaceOrder($owner['headers'], $product['id']);

    $this->client->jsonRequest('POST', '/orders/'.$order['id'].'/mark-paid', [], $owner['headers']);

    $response = $this->client->getResponse();
    $payload = json_decode((string) $response->getContent(), true, flags: JSON_THROW_ON_ERROR);

    expect($response->getStatusCode())->toBe(403)
        ->and($payload['error']['code'])->toBe('forbidden');
});

it('rejects unauthenticated mark paid', function () {
    $this->client->jsonRequest('POST', '/orders/aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa/mark-paid');

    $response = $this->client->getResponse();
    $payload = json_decode((string) $response->getContent(), true, flags: JSON_THROW_ON_ERROR);

    expect($response->getStatusCode())->toBe(401)
        ->and($payload['error']['code'])->toBe('unauthenticated');
});

it('rejects marking a cancelled order as paid', function () {
    $product = markPaidLifecycleProduct();
    $owner = markPaidLifecycleLogin('paid-owner@nuvora.test');
    $order = markPaidLifecyclePlaceOrder($owner['headers'], $product['id']);

    $this->client->jsonRequest('POST', '/orders/'.$order['id'].'/cancel', [], $owner['headers']);
    expect($this->client->getResponse()->getStatusCode())->toBe(200);

    $this->client->jsonRequest('POST', '/orders/'.$order['id'].'/mark-paid', [], catalogAdminHeaders());

    $response = $this->client->getResponse();
    $payload = json_decode((string) $response->getContent(), true, flags: JSON_THROW_ON_ERROR);

    expect($response->getStatusCode())->toBe(409)
        ->and($payload['error']['code'])->toBe('invalid_order_transition');
});
