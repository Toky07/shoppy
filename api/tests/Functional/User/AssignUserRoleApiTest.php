<?php

declare(strict_types=1);

it('lets an admin assign a role', function () {
    $target = registerCatalogUser($this->client, 'ada@nuvora.test');

    $this->client->jsonRequest('PATCH', '/users/'.$target['id'], [
        'role' => 'admin',
    ], catalogAdminHeaders());

    $response = $this->client->getResponse();
    $payload = json_decode((string) $response->getContent(), true, flags: JSON_THROW_ON_ERROR);

    expect($response->getStatusCode())->toBe(200)
        ->and($payload['id'])->toBe($target['id'])
        ->and($payload['email'])->toBe('ada@nuvora.test')
        ->and($payload['role'])->toBe('admin')
        ->and($payload)->not->toHaveKey('password');
});

it('revokes the previous token when an admin changes the role', function () {
    $target = registerCatalogUser($this->client, 'ada@nuvora.test');
    $this->client->jsonRequest('POST', '/auth/login', [
        'email' => 'ada@nuvora.test',
        'password' => 'secret-secret',
    ]);
    $targetHeaders = ['HTTP_AUTHORIZATION' => 'Bearer '.sessionAccessToken($this->client)];

    $this->client->jsonRequest('PATCH', '/users/'.$target['id'], [
        'role' => 'admin',
    ], catalogAdminHeaders());

    $this->client->jsonRequest('POST', '/products', [
        'name' => 'Nuvora Tee',
        'priceCents' => 1999,
    ], $targetHeaders);

    expect($this->client->getResponse()->getStatusCode())->toBe(401);

    $this->client->jsonRequest('POST', '/auth/login', [
        'email' => 'ada@nuvora.test',
        'password' => 'secret-secret',
    ]);

    $this->client->jsonRequest('POST', '/products', [
        'name' => 'Nuvora Tee',
        'priceCents' => 1999,
    ], ['HTTP_AUTHORIZATION' => 'Bearer '.sessionAccessToken($this->client)]);

    expect($this->client->getResponse()->getStatusCode())->toBe(201);
});

it('rejects unauthenticated role assignment', function () {
    $target = registerCatalogUser($this->client, 'ada@nuvora.test');

    $this->client->jsonRequest('PATCH', '/users/'.$target['id'], [
        'role' => 'admin',
    ]);

    $response = $this->client->getResponse();
    $payload = json_decode((string) $response->getContent(), true, flags: JSON_THROW_ON_ERROR);

    expect($response->getStatusCode())->toBe(401)
        ->and($payload['error'])->toBe([
            'code' => 'unauthenticated',
            'message' => 'The request is not authenticated.',
        ]);
});

it('rejects a customer assigning a role', function () {
    $target = registerCatalogUser($this->client, 'ada@nuvora.test');

    $this->client->jsonRequest('PATCH', '/users/'.$target['id'], [
        'role' => 'admin',
    ], catalogCustomerHeaders());

    $response = $this->client->getResponse();
    $payload = json_decode((string) $response->getContent(), true, flags: JSON_THROW_ON_ERROR);

    expect($response->getStatusCode())->toBe(403)
        ->and($payload['error']['code'])->toBe('forbidden')
        ->and((string) $response->getContent())->not->toContain('ada@nuvora.test');
});

it('returns not found for an unknown user', function () {
    $this->client->jsonRequest('PATCH', '/users/550e8400-e29b-41d4-a716-446655440000', [
        'role' => 'admin',
    ], catalogAdminHeaders());

    $response = $this->client->getResponse();
    $payload = json_decode((string) $response->getContent(), true, flags: JSON_THROW_ON_ERROR);

    expect($response->getStatusCode())->toBe(404)
        ->and($payload['error'])->toBe([
            'code' => 'user_not_found',
            'message' => 'The requested resource was not found.',
        ])
        ->and((string) $response->getContent())->not->toContain('550e8400');
});

it('rejects an unknown role', function () {
    $target = registerCatalogUser($this->client, 'ada@nuvora.test');

    $this->client->jsonRequest('PATCH', '/users/'.$target['id'], [
        'role' => 'superuser',
    ], catalogAdminHeaders());

    $response = $this->client->getResponse();
    $payload = json_decode((string) $response->getContent(), true, flags: JSON_THROW_ON_ERROR);

    expect($response->getStatusCode())->toBe(400)
        ->and($payload['error']['code'])->toBe('validation_error')
        ->and($payload['error']['violations'])->toBe([
            ['field' => 'role', 'message' => 'Role must be customer or admin.'],
        ]);
});

it('rejects a missing role', function () {
    $target = registerCatalogUser($this->client, 'ada@nuvora.test');

    $this->client->jsonRequest('PATCH', '/users/'.$target['id'], [], catalogAdminHeaders());

    $response = $this->client->getResponse();
    $payload = json_decode((string) $response->getContent(), true, flags: JSON_THROW_ON_ERROR);

    expect($response->getStatusCode())->toBe(400)
        ->and($payload['error']['code'])->toBe('validation_error')
        ->and($payload['error']['violations'])->toBe([
            ['field' => 'role', 'message' => 'This field is required.'],
        ]);
});

it('lets an admin demote a user to customer', function () {
    $target = registerCatalogUser($this->client, 'ada@nuvora.test');
    $this->client->jsonRequest('PATCH', '/users/'.$target['id'], [
        'role' => 'admin',
    ], catalogAdminHeaders());

    $this->client->jsonRequest('PATCH', '/users/'.$target['id'], [
        'role' => 'customer',
    ], catalogAdminHeaders());

    $payload = json_decode((string) $this->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);

    expect($this->client->getResponse()->getStatusCode())->toBe(200)
        ->and($payload['role'])->toBe('customer');
});
