<?php

declare(strict_types=1);

function listOrdersProduct(string $name, int $priceCents): array
{
    test()->client->jsonRequest('POST', '/products', [
        'name' => $name,
        'priceCents' => $priceCents,
        'stock' => 100,
    ], catalogAdminHeaders());

    return json_decode((string) test()->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);
}

function loginListOrdersUser(string $email): array
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

function placeListedOrder(array $headers, string $productId, int $quantity = 1): array
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

it('lists the current customer orders newest first', function () {
    $olderProduct = listOrdersProduct('Older Tee', 1999);
    $newerProduct = listOrdersProduct('Newer Tee', 2999);
    $owner = loginListOrdersUser('owner@nuvora.test');
    $other = loginListOrdersUser('other@nuvora.test');

    $older = placeListedOrder($owner['headers'], $olderProduct['id'], 1);
    usleep(1_100_000);
    $newer = placeListedOrder($owner['headers'], $newerProduct['id'], 2);
    placeListedOrder($other['headers'], $olderProduct['id'], 1);

    $this->client->jsonRequest('GET', '/orders', [], $owner['headers']);

    $response = $this->client->getResponse();
    $payload = json_decode((string) $response->getContent(), true, flags: JSON_THROW_ON_ERROR);

    expect($response->getStatusCode())->toBe(200)
        ->and($payload['page'])->toBe(1)
        ->and($payload['limit'])->toBe(20)
        ->and($payload['total'])->toBe(2)
        ->and($payload['items'])->toHaveCount(2)
        ->and($payload['items'][0]['id'])->toBe($newer['id'])
        ->and($payload['items'][0]['customerId'])->toBe($owner['user']['id'])
        ->and($payload['items'][0]['items'][0]['name'])->toBe('Newer Tee')
        ->and($payload['items'][0]['total'])->toBe([
            'cents' => 5998,
            'currency' => 'EUR',
        ])
        ->and($payload['items'][1]['id'])->toBe($older['id']);
});

it('paginates the current customer orders', function () {
    $one = listOrdersProduct('One', 1000);
    $two = listOrdersProduct('Two', 2000);
    $three = listOrdersProduct('Three', 3000);
    $owner = loginListOrdersUser('owner@nuvora.test');

    $oldest = placeListedOrder($owner['headers'], $one['id'], 1);
    usleep(1_100_000);
    placeListedOrder($owner['headers'], $two['id'], 1);
    usleep(1_100_000);
    placeListedOrder($owner['headers'], $three['id'], 1);

    $this->client->jsonRequest('GET', '/orders?page=2&limit=2', [], $owner['headers']);

    $response = $this->client->getResponse();
    $payload = json_decode((string) $response->getContent(), true, flags: JSON_THROW_ON_ERROR);

    expect($response->getStatusCode())->toBe(200)
        ->and($payload['page'])->toBe(2)
        ->and($payload['limit'])->toBe(2)
        ->and($payload['total'])->toBe(3)
        ->and($payload['items'])->toHaveCount(1)
        ->and($payload['items'][0]['id'])->toBe($oldest['id']);
});

it('returns an empty list when the current customer has no orders', function () {
    $owner = loginListOrdersUser('owner@nuvora.test');

    $this->client->jsonRequest('GET', '/orders', [], $owner['headers']);

    $response = $this->client->getResponse();
    $payload = json_decode((string) $response->getContent(), true, flags: JSON_THROW_ON_ERROR);

    expect($response->getStatusCode())->toBe(200)
        ->and($payload)->toBe([
            'items' => [],
            'page' => 1,
            'limit' => 20,
            'total' => 0,
        ]);
});

it('rejects unauthenticated order listing', function () {
    $this->client->jsonRequest('GET', '/orders');

    $response = $this->client->getResponse();
    $payload = json_decode((string) $response->getContent(), true, flags: JSON_THROW_ON_ERROR);

    expect($response->getStatusCode())->toBe(401)
        ->and($payload['error'])->toBe([
            'code' => 'unauthenticated',
            'message' => 'The request is not authenticated.',
        ]);
});

it('rejects invalid pagination', function () {
    $owner = loginListOrdersUser('owner@nuvora.test');

    $this->client->jsonRequest('GET', '/orders?page=0', [], $owner['headers']);

    $response = $this->client->getResponse();
    $payload = json_decode((string) $response->getContent(), true, flags: JSON_THROW_ON_ERROR);

    expect($response->getStatusCode())->toBe(400)
        ->and($payload['error']['code'])->toBe('validation_error')
        ->and($payload['error']['violations'])->toBe([
            ['field' => 'page', 'message' => 'Page must be at least 1 and limit must be between 1 and 100.'],
        ]);
});
