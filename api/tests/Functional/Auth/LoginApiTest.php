<?php

declare(strict_types=1);

use Symfony\Bundle\FrameworkBundle\KernelBrowser;

function registerAccount(KernelBrowser $client): void
{
    $client->jsonRequest('POST', '/users', [
        'email' => 'Ada@Nuvora.test',
        'password' => 'secret-secret',
    ]);
}

it('logs in with valid credentials without exposing the password', function () {
    registerAccount($this->client);

    $this->client->jsonRequest('POST', '/auth/login', [
        'email' => 'ADA@nuvora.test',
        'password' => 'secret-secret',
    ]);

    $response = $this->client->getResponse();
    $payload = json_decode((string) $response->getContent(), true, flags: JSON_THROW_ON_ERROR);

    expect($response->getStatusCode())->toBe(200)
        ->and($payload['user']['email'])->toBe('ada@nuvora.test')
        ->and($payload['user']['role'])->toBe('customer')
        ->and($payload['user'])->toHaveKey('id')
        ->and($payload['user'])->toHaveKey('createdAt')
        ->and($payload['accessToken'])->toMatch('/^[0-9a-f]{64}$/')
        ->and($payload)->not->toHaveKey('password')
        ->and($payload)->not->toHaveKey('passwordHash')
        ->and($payload['user'])->not->toHaveKey('password')
        ->and((string) $response->getContent())->not->toContain('secret-secret')
        ->and((string) $response->getContent())->not->toContain('$argon2id$');
});

it('rejects a wrong password without leaking the email', function () {
    registerAccount($this->client);

    $this->client->jsonRequest('POST', '/auth/login', [
        'email' => 'ada@nuvora.test',
        'password' => 'wrong-password',
    ]);

    $response = $this->client->getResponse();
    $payload = json_decode((string) $response->getContent(), true, flags: JSON_THROW_ON_ERROR);

    expect($response->getStatusCode())->toBe(401)
        ->and($payload['error'])->toBe([
            'code' => 'invalid_credentials',
            'message' => 'The provided credentials are invalid.',
        ])
        ->and((string) $response->getContent())->not->toContain('ada@nuvora.test');
});

it('rejects an unknown email with the same error as a wrong password', function () {
    $this->client->jsonRequest('POST', '/auth/login', [
        'email' => 'unknown@nuvora.test',
        'password' => 'secret-secret',
    ]);
    $unknown = json_decode((string) $this->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);

    registerAccount($this->client);
    $this->client->jsonRequest('POST', '/auth/login', [
        'email' => 'ada@nuvora.test',
        'password' => 'wrong-password',
    ]);
    $wrongPassword = json_decode((string) $this->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);

    expect($this->client->getResponse()->getStatusCode())->toBe(401)
        ->and($unknown)->toBe($wrongPassword)
        ->and((string) json_encode($unknown))->not->toContain('unknown@nuvora.test');
});

it('rejects a missing password', function () {
    $this->client->jsonRequest('POST', '/auth/login', [
        'email' => 'ada@nuvora.test',
    ]);

    $response = $this->client->getResponse();
    $payload = json_decode((string) $response->getContent(), true, flags: JSON_THROW_ON_ERROR);

    expect($response->getStatusCode())->toBe(400)
        ->and($payload['error']['code'])->toBe('validation_error')
        ->and($payload['error']['violations'])->toBe([
            ['field' => 'password', 'message' => 'This field is required.'],
        ]);
});
