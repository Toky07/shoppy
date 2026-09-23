<?php

declare(strict_types=1);

it('registers an account without exposing the password', function () {
    $this->client->jsonRequest('POST', '/users', [
        'email' => 'Ada@Nuvora.test',
        'password' => 'secret-secret',
    ]);

    $response = $this->client->getResponse();
    $payload = json_decode((string) $response->getContent(), true, flags: JSON_THROW_ON_ERROR);

    expect($response->getStatusCode())->toBe(201)
        ->and($payload)->toBe(['status' => 'accepted'])
        ->and($payload)->not->toHaveKey('email')
        ->and($payload)->not->toHaveKey('id')
        ->and((string) $response->getContent())->not->toContain('secret-secret')
        ->and((string) $response->getContent())->not->toContain('ada@nuvora.test')
        ->and((string) $response->getContent())->not->toContain('$argon2id$')
        ->and($response->headers->get('Location'))->toBeNull();
});

it('rejects a missing email', function () {
    $this->client->jsonRequest('POST', '/users', [
        'password' => 'secret-secret',
    ]);

    $response = $this->client->getResponse();
    $payload = json_decode((string) $response->getContent(), true, flags: JSON_THROW_ON_ERROR);

    expect($response->getStatusCode())->toBe(400)
        ->and($payload['error']['code'])->toBe('validation_error')
        ->and($payload['error']['violations'])->toBe([
            ['field' => 'email', 'message' => 'This field is required.'],
        ]);
});

it('rejects a weak password', function () {
    $this->client->jsonRequest('POST', '/users', [
        'email' => 'ada@nuvora.test',
        'password' => 'short',
    ]);

    $response = $this->client->getResponse();
    $payload = json_decode((string) $response->getContent(), true, flags: JSON_THROW_ON_ERROR);

    expect($response->getStatusCode())->toBe(400)
        ->and($payload['error']['code'])->toBe('validation_error')
        ->and($payload['error']['violations'])->toBe([
            ['field' => 'password', 'message' => 'Password must be between 12 and 4096 characters.'],
        ]);
});

it('accepts a duplicate email with the same response as a new account', function () {
    $this->client->jsonRequest('POST', '/users', [
        'email' => 'ada@nuvora.test',
        'password' => 'secret-secret',
    ]);
    $this->client->jsonRequest('POST', '/users', [
        'email' => 'ADA@nuvora.test',
        'password' => 'another-secret',
    ]);

    $response = $this->client->getResponse();
    $payload = json_decode((string) $response->getContent(), true, flags: JSON_THROW_ON_ERROR);

    expect($response->getStatusCode())->toBe(201)
        ->and($payload)->toBe(['status' => 'accepted'])
        ->and((string) $response->getContent())->not->toContain('ada@nuvora.test');
});

it('ignores a client-supplied role and registers as customer', function () {
    $this->client->jsonRequest('POST', '/users', [
        'email' => 'ada@nuvora.test',
        'password' => 'secret-secret',
        'role' => 'admin',
    ]);

    $response = $this->client->getResponse();
    $payload = json_decode((string) $response->getContent(), true, flags: JSON_THROW_ON_ERROR);

    expect($response->getStatusCode())->toBe(201)
        ->and($payload)->toBe(['status' => 'accepted'])
        ->and($payload)->not->toHaveKey('role');
});
