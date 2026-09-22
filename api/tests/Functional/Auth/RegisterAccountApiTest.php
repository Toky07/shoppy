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
        ->and($payload['email'])->toBe('ada@nuvora.test')
        ->and($payload['role'])->toBe('customer')
        ->and($payload['emailVerified'])->toBeFalse()
        ->and($payload)->toHaveKey('id')
        ->and($payload)->toHaveKey('createdAt')
        ->and($payload)->not->toHaveKey('password')
        ->and($payload)->not->toHaveKey('passwordHash')
        ->and((string) $response->getContent())->not->toContain('secret-secret')
        ->and((string) $response->getContent())->not->toContain('$argon2id$')
        ->and($response->headers->get('Location'))->toBe('/users/'.$payload['id']);
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
            ['field' => 'password', 'message' => 'Password must be between 8 and 4096 characters.'],
        ]);
});

it('rejects a duplicate email without leaking the address in the message', function () {
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

    expect($response->getStatusCode())->toBe(409)
        ->and($payload['error'])->toBe([
            'code' => 'email_already_registered',
            'message' => 'The request conflicts with the current state.',
        ])
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
        ->and($payload['role'])->toBe('customer');
});
