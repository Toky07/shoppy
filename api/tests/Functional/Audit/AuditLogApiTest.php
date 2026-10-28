<?php

declare(strict_types=1);

it('lists admin changes newest first', function () {
    $target = registerCatalogUser($this->client, 'audit-ada@nuvora.test');
    $this->client->jsonRequest('PATCH', '/users/'.$target['id'], [
        'role' => 'admin',
    ], catalogAdminHeaders());

    $this->client->jsonRequest('POST', '/products', [
        'name' => 'Audit Tee',
        'priceCents' => 1999,
        'stock' => 10,
    ], catalogAdminHeaders());
    $product = json_decode((string) $this->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);

    $this->client->jsonRequest('PATCH', '/products/'.$product['id'], [
        'priceCents' => 2499,
    ], catalogAdminHeaders());
    $this->client->jsonRequest('PUT', '/products/'.$product['id'].'/stock', [
        'stock' => 3,
    ], catalogAdminHeaders());

    registerCatalogUser($this->client, 'audit-buyer@nuvora.test');
    $this->client->jsonRequest('POST', '/auth/login', [
        'email' => 'audit-buyer@nuvora.test',
        'password' => 'secret-secret',
    ]);
    $headers = ['HTTP_AUTHORIZATION' => 'Bearer '.sessionAccessToken($this->client)];
    $order = placeOrderFromCart($headers, $product['id']);
    $this->client->jsonRequest('POST', '/orders/'.$order['id'].'/mark-paid', [], catalogAdminHeaders());

    $this->client->jsonRequest('GET', '/admin/audit', [], catalogAdminHeaders());

    $response = $this->client->getResponse();
    $payload = json_decode((string) $response->getContent(), true, flags: JSON_THROW_ON_ERROR);
    $byAction = array_column($payload['items'], null, 'action');

    expect($response->getStatusCode())->toBe(200)
        ->and($payload['page'])->toBe(1)
        ->and($payload['limit'])->toBe(20)
        ->and($payload['total'])->toBe(4)
        ->and($payload['items'][0]['action'])->toBe('payment_changed')
        ->and($byAction['payment_changed']['subjectId'])->toBe($order['id'])
        ->and($byAction['payment_changed']['actorEmail'])->toBe('admin@nuvora.test')
        ->and($byAction['stock_changed']['metadata'])->toEqual([
            'from' => 10,
            'to' => 3,
            'label' => 'Audit Tee',
        ])
        ->and($byAction['price_changed']['metadata'])->toEqual([
            'fromCents' => 1999,
            'toCents' => 2499,
            'label' => 'Audit Tee',
        ])
        ->and($byAction['role_changed']['subjectId'])->toBe($target['id'])
        ->and($byAction['role_changed']['metadata'])->toEqual([
            'from' => 'customer',
            'to' => 'admin',
            'label' => 'audit-ada@nuvora.test',
        ])
        ->and($byAction['payment_changed']['metadata']['label'])->toBe('Audit Tee');
});

it('rejects a customer reading the audit log', function () {
    $this->client->jsonRequest('GET', '/admin/audit', [], catalogCustomerHeaders());

    $response = $this->client->getResponse();
    $payload = json_decode((string) $response->getContent(), true, flags: JSON_THROW_ON_ERROR);

    expect($response->getStatusCode())->toBe(403)
        ->and($payload['error']['code'])->toBe('forbidden');
});

it('rejects unauthenticated access to the audit log', function () {
    $this->client->jsonRequest('GET', '/admin/audit');

    $response = $this->client->getResponse();
    $payload = json_decode((string) $response->getContent(), true, flags: JSON_THROW_ON_ERROR);

    expect($response->getStatusCode())->toBe(401)
        ->and($payload['error']['code'])->toBe('unauthenticated');
});
