<?php

declare(strict_types=1);

function adminDashboardProduct(string $name, int $priceCents, int $stock): array
{
    test()->client->jsonRequest('POST', '/products', [
        'name' => $name,
        'priceCents' => $priceCents,
        'stock' => $stock,
    ], catalogAdminHeaders());

    return json_decode((string) test()->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);
}

function adminDashboardLogin(string $email): array
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

function adminDashboardMarkPaid(string $orderId): void
{
    test()->client->jsonRequest('POST', '/orders/'.$orderId.'/mark-paid', [], catalogAdminHeaders());
}

it('returns an empty dashboard for an admin', function () {
    $this->client->jsonRequest('GET', '/admin/dashboard', [], catalogAdminHeaders());

    $response = $this->client->getResponse();
    $payload = json_decode((string) $response->getContent(), true, flags: JSON_THROW_ON_ERROR);

    expect($response->getStatusCode())->toBe(200)
        ->and($payload)->toEqual([
            'revenue' => ['cents' => 0, 'currency' => 'EUR'],
            'today' => [
                'orders' => 0,
                'revenue' => ['cents' => 0, 'currency' => 'EUR'],
            ],
            'lowStock' => [],
        ]);
});

it('summarises paid revenue, today\'s orders and low stock', function () {
    $catalog = adminDashboardProduct('Dashboard Hoodie', 4999, 40);
    $low = adminDashboardProduct('Dashboard Mug', 1299, 1);
    $ok = adminDashboardProduct('Dashboard Tee', 1999, 12);
    $customer = adminDashboardLogin('dashboard-owner@nuvora.test');

    $older = placeOrderFromCart($customer['headers'], $catalog['id']);
    adminDashboardMarkPaid($older['id']);
    backdateOrder($this->client, $older['id'], '2026-01-15T12:00:00+00:00');

    $todayPaid = placeOrderFromCart($customer['headers'], $catalog['id']);
    adminDashboardMarkPaid($todayPaid['id']);

    placeOrderFromCart($customer['headers'], $catalog['id']);

    $this->client->jsonRequest('GET', '/admin/dashboard', [], catalogAdminHeaders());

    $response = $this->client->getResponse();
    $payload = json_decode((string) $response->getContent(), true, flags: JSON_THROW_ON_ERROR);

    expect($response->getStatusCode())->toBe(200)
        ->and($payload['revenue']['cents'])->toBe($older['total']['cents'] + $todayPaid['total']['cents'])
        ->and($payload['revenue']['currency'])->toBe('EUR')
        ->and($payload['today']['orders'])->toBe(2)
        ->and($payload['today']['revenue']['cents'])->toBe($todayPaid['total']['cents'])
        ->and($payload['lowStock'])->toEqual([
            [
                'id' => $low['id'],
                'name' => 'Dashboard Mug',
                'slug' => $low['slug'],
                'stock' => 1,
            ],
        ])
        ->and($payload['lowStock'][0]['id'])->not->toBe($ok['id']);
});

it('rejects a customer reading the dashboard', function () {
    $this->client->jsonRequest('GET', '/admin/dashboard', [], catalogCustomerHeaders());

    $response = $this->client->getResponse();
    $payload = json_decode((string) $response->getContent(), true, flags: JSON_THROW_ON_ERROR);

    expect($response->getStatusCode())->toBe(403)
        ->and($payload['error']['code'])->toBe('forbidden');
});

it('rejects unauthenticated access to the dashboard', function () {
    $this->client->jsonRequest('GET', '/admin/dashboard');

    $response = $this->client->getResponse();
    $payload = json_decode((string) $response->getContent(), true, flags: JSON_THROW_ON_ERROR);

    expect($response->getStatusCode())->toBe(401)
        ->and($payload['error']['code'])->toBe('unauthenticated');
});
