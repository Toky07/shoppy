<?php

declare(strict_types=1);

function adminListOrdersProduct(string $name, int $priceCents): array
{
    test()->client->jsonRequest('POST', '/products', [
        'name' => $name,
        'priceCents' => $priceCents,
        'stock' => 100,
    ], catalogAdminHeaders());

    return json_decode((string) test()->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);
}

function adminListOrdersLogin(string $email): array
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

function adminListOrdersPlace(array $headers, string $productId, int $quantity = 1): array
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

it('lists all orders for an admin newest first', function () {
    $olderProduct = adminListOrdersProduct('Older Tee', 1999);
    $newerProduct = adminListOrdersProduct('Newer Tee', 2999);
    $first = adminListOrdersLogin('admin-list-a@nuvora.test');
    $second = adminListOrdersLogin('admin-list-b@nuvora.test');

    $older = adminListOrdersPlace($first['headers'], $olderProduct['id'], 1);
    usleep(1_100_000);
    $newer = adminListOrdersPlace($second['headers'], $newerProduct['id'], 2);

    $this->client->jsonRequest('GET', '/admin/orders', [], catalogAdminHeaders());

    $response = $this->client->getResponse();
    $payload = json_decode((string) $response->getContent(), true, flags: JSON_THROW_ON_ERROR);

    expect($response->getStatusCode())->toBe(200)
        ->and($payload['page'])->toBe(1)
        ->and($payload['limit'])->toBe(20)
        ->and($payload['total'])->toBe(2)
        ->and($payload['items'])->toHaveCount(2)
        ->and($payload['items'][0]['id'])->toBe($newer['id'])
        ->and($payload['items'][0]['customerId'])->toBe($second['user']['id'])
        ->and($payload['items'][1]['id'])->toBe($older['id'])
        ->and($payload['items'][1]['customerId'])->toBe($first['user']['id']);
});

it('paginates all orders for an admin', function () {
    $one = adminListOrdersProduct('One', 1000);
    $two = adminListOrdersProduct('Two', 2000);
    $three = adminListOrdersProduct('Three', 3000);
    $owner = adminListOrdersLogin('admin-list-owner@nuvora.test');

    $oldest = adminListOrdersPlace($owner['headers'], $one['id']);
    usleep(1_100_000);
    adminListOrdersPlace($owner['headers'], $two['id']);
    usleep(1_100_000);
    adminListOrdersPlace($owner['headers'], $three['id']);

    $this->client->jsonRequest('GET', '/admin/orders?page=2&limit=2', [], catalogAdminHeaders());

    $response = $this->client->getResponse();
    $payload = json_decode((string) $response->getContent(), true, flags: JSON_THROW_ON_ERROR);

    expect($response->getStatusCode())->toBe(200)
        ->and($payload['page'])->toBe(2)
        ->and($payload['limit'])->toBe(2)
        ->and($payload['total'])->toBe(3)
        ->and($payload['items'])->toHaveCount(1)
        ->and($payload['items'][0]['id'])->toBe($oldest['id']);
});

it('rejects a customer listing all orders', function () {
    $this->client->jsonRequest('GET', '/admin/orders', [], catalogCustomerHeaders());

    $response = $this->client->getResponse();
    $payload = json_decode((string) $response->getContent(), true, flags: JSON_THROW_ON_ERROR);

    expect($response->getStatusCode())->toBe(403)
        ->and($payload['error']['code'])->toBe('forbidden');
});

it('rejects unauthenticated listing of all orders', function () {
    $this->client->jsonRequest('GET', '/admin/orders');

    $response = $this->client->getResponse();
    $payload = json_decode((string) $response->getContent(), true, flags: JSON_THROW_ON_ERROR);

    expect($response->getStatusCode())->toBe(401)
        ->and($payload['error']['code'])->toBe('unauthenticated');
});
