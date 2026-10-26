<?php

declare(strict_types=1);

it('rotates the session cookies from a refresh token', function () {
    $this->client->jsonRequest('POST', '/users', [
        'email' => 'ada@nuvora.test',
        'password' => 'secret-secret',
    ]);
    $this->client->jsonRequest('POST', '/auth/login', [
        'email' => 'ada@nuvora.test',
        'password' => 'secret-secret',
    ]);

    $access = $this->client->getCookieJar()->get('shoppy_session')?->getValue();
    $refresh = $this->client->getCookieJar()->get('shoppy_refresh')?->getValue();

    expect($access)->not->toBeNull()
        ->and($refresh)->not->toBeNull();

    $this->client->jsonRequest('POST', '/auth/refresh');

    $rotatedAccess = $this->client->getCookieJar()->get('shoppy_session')?->getValue();
    $rotatedRefresh = $this->client->getCookieJar()->get('shoppy_refresh')?->getValue();

    expect($this->client->getResponse()->getStatusCode())->toBe(204)
        ->and($rotatedAccess)->not->toBe($access)
        ->and($rotatedRefresh)->not->toBe($refresh);

    $this->client->jsonRequest('GET', '/auth/me');

    expect($this->client->getResponse()->getStatusCode())->toBe(200);
});

it('rejects refresh without a refresh cookie', function () {
    $this->client->jsonRequest('POST', '/auth/refresh');

    $payload = json_decode((string) $this->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);

    expect($this->client->getResponse()->getStatusCode())->toBe(401)
        ->and($payload['error']['code'])->toBe('unauthenticated');
});
