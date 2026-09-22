<?php

declare(strict_types=1);

function orderProduct(string $name = 'Nuvora Tee', int $priceCents = 1999): array
{
    test()->client->jsonRequest('POST', '/products', [
        'name' => $name,
        'priceCents' => $priceCents,
        'stock' => 100,
    ], catalogAdminHeaders());

    return json_decode((string) test()->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);
}

function loginOrderUser(string $email): array
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

function placeOrderForCurrentUser(array $headers, string $productId, int $quantity = 2): array
{
    test()->client->jsonRequest('POST', '/orders', [
        ...deliveryFields(),
        'items' => [[
            'productId' => $productId,
            'quantity' => $quantity,
        ]],
    ], $headers);

    return json_decode((string) test()->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);
}

it('returns an order to its owner', function () {
    $product = orderProduct();
    $owner = loginOrderUser('owner@nuvora.test');
    $created = placeOrderForCurrentUser($owner['headers'], $product['id']);

    $this->client->jsonRequest('GET', '/orders/'.$created['id'], [], $owner['headers']);

    $response = $this->client->getResponse();
    $payload = json_decode((string) $response->getContent(), true, flags: JSON_THROW_ON_ERROR);

    expect($response->getStatusCode())->toBe(200)
        ->and($payload['id'])->toBe($created['id'])
        ->and($payload['customerId'])->toBe($owner['user']['id'])
        ->and($payload['items'][0]['productId'])->toBe($product['id'])
        ->and($payload['total'])->toBe([
            'cents' => 3998,
            'currency' => 'EUR',
        ]);
});

it('rejects unauthenticated order reads', function () {
    $this->client->jsonRequest('GET', '/orders/aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa');

    $response = $this->client->getResponse();
    $payload = json_decode((string) $response->getContent(), true, flags: JSON_THROW_ON_ERROR);

    expect($response->getStatusCode())->toBe(401)
        ->and($payload['error'])->toBe([
            'code' => 'unauthenticated',
            'message' => 'The request is not authenticated.',
        ]);
});

it('returns an order to an admin', function () {
    $product = orderProduct();
    $owner = loginOrderUser('owner@nuvora.test');
    $created = placeOrderForCurrentUser($owner['headers'], $product['id']);

    $this->client->jsonRequest('GET', '/orders/'.$created['id'], [], catalogAdminHeaders());

    $response = $this->client->getResponse();
    $payload = json_decode((string) $response->getContent(), true, flags: JSON_THROW_ON_ERROR);

    expect($response->getStatusCode())->toBe(200)
        ->and($payload['id'])->toBe($created['id'])
        ->and($payload['customerId'])->toBe($owner['user']['id']);
});

it('rejects reading another customer order', function () {
    $product = orderProduct();
    $owner = loginOrderUser('owner@nuvora.test');
    $intruder = loginOrderUser('intruder@nuvora.test');
    $created = placeOrderForCurrentUser($owner['headers'], $product['id']);

    $this->client->jsonRequest('GET', '/orders/'.$created['id'], [], $intruder['headers']);

    $response = $this->client->getResponse();
    $payload = json_decode((string) $response->getContent(), true, flags: JSON_THROW_ON_ERROR);

    expect($response->getStatusCode())->toBe(403)
        ->and($payload['error'])->toBe([
            'code' => 'forbidden',
            'message' => 'The request is not authorized.',
        ])
        ->and((string) $response->getContent())->not->toContain($owner['user']['id']);
});

it('returns not found for an unknown order', function () {
    $owner = loginOrderUser('owner@nuvora.test');

    $this->client->jsonRequest('GET', '/orders/aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa', [], $owner['headers']);

    $response = $this->client->getResponse();
    $payload = json_decode((string) $response->getContent(), true, flags: JSON_THROW_ON_ERROR);

    expect($response->getStatusCode())->toBe(404)
        ->and($payload['error'])->toBe([
            'code' => 'order_not_found',
            'message' => 'The requested resource was not found.',
        ])
        ->and((string) $response->getContent())->not->toContain('aaaaaaaa');
});
