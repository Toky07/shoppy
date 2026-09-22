<?php

declare(strict_types=1);

it('accepts a password reset request without revealing whether the email exists', function () {
    $this->client->jsonRequest('POST', '/auth/password-resets', [
        'email' => 'missing@shoppy.test',
    ]);

    expect($this->client->getResponse()->getStatusCode())->toBe(204);

    $headers = catalogCustomerHeaders();
    $this->client->jsonRequest('POST', '/auth/password-resets', [
        'email' => 'customer@nuvora.test',
    ]);

    expect($this->client->getResponse()->getStatusCode())->toBe(204)
        ->and($headers)->not->toBeEmpty();
});

it('rejects an invalid password reset token', function () {
    $this->client->jsonRequest('POST', '/auth/password-resets/confirm', [
        'token' => str_repeat('a', 64),
        'password' => 'brand-new-secret',
    ]);

    expect($this->client->getResponse()->getStatusCode())->toBe(400);
});

it('changes the password and revokes the current session', function () {
    $headers = catalogCustomerHeaders();
    $this->client->jsonRequest('POST', '/auth/password', [
        'currentPassword' => 'secret-secret',
        'newPassword' => 'brand-new-secret',
    ], $headers);

    expect($this->client->getResponse()->getStatusCode())->toBe(204);

    $this->client->jsonRequest('GET', '/auth/me', server: $headers);

    expect($this->client->getResponse()->getStatusCode())->toBe(401);

    $this->client->jsonRequest('POST', '/auth/login', [
        'email' => 'customer@nuvora.test',
        'password' => 'brand-new-secret',
    ]);

    expect($this->client->getResponse()->getStatusCode())->toBe(200);
});

it('logs out every session', function () {
    $headers = catalogCustomerHeaders();
    $this->client->jsonRequest('POST', '/auth/logout-all', server: $headers);

    expect($this->client->getResponse()->getStatusCode())->toBe(204);

    $this->client->jsonRequest('GET', '/auth/me', server: $headers);

    expect($this->client->getResponse()->getStatusCode())->toBe(401);
});

it('anonymizes the signed-in account', function () {
    $this->client->jsonRequest('POST', '/users', [
        'email' => 'gone@shoppy.test',
        'password' => 'secret-secret',
    ]);
    $this->client->jsonRequest('POST', '/auth/login', [
        'email' => 'gone@shoppy.test',
        'password' => 'secret-secret',
    ]);
    $token = json_decode((string) $this->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR)['accessToken'];
    $headers = ['HTTP_AUTHORIZATION' => 'Bearer '.$token];

    $this->client->jsonRequest('POST', '/auth/account/deletion', [
        'password' => 'secret-secret',
    ], $headers);

    expect($this->client->getResponse()->getStatusCode())->toBe(204);

    $this->client->jsonRequest('POST', '/auth/login', [
        'email' => 'gone@shoppy.test',
        'password' => 'secret-secret',
    ]);

    expect($this->client->getResponse()->getStatusCode())->toBe(401);
});

it('refuses to delete the last admin', function () {
    $headers = catalogAdminHeaders();
    $this->client->jsonRequest('POST', '/auth/account/deletion', [
        'password' => 'secret-secret',
    ], $headers);

    $payload = json_decode((string) $this->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);

    expect($this->client->getResponse()->getStatusCode())->toBe(409)
        ->and($payload['error']['code'])->toBe('last_admin_account');
});
