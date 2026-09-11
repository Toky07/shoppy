<?php

declare(strict_types=1);

use Symfony\Bundle\FrameworkBundle\KernelBrowser;

function loginAccessToken(KernelBrowser $client): string
{
    $client->jsonRequest('POST', '/users', [
        'email' => 'Ada@Nuvora.test',
        'password' => 'secret-secret',
    ]);
    $client->jsonRequest('POST', '/auth/login', [
        'email' => 'ada@nuvora.test',
        'password' => 'secret-secret',
    ]);

    $payload = json_decode((string) $client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);

    return $payload['accessToken'];
}

it('returns the current user for a valid bearer token', function () {
    $token = loginAccessToken($this->client);

    $this->client->jsonRequest('GET', '/auth/me', [], [
        'HTTP_AUTHORIZATION' => 'Bearer '.$token,
    ]);

    $response = $this->client->getResponse();
    $payload = json_decode((string) $response->getContent(), true, flags: JSON_THROW_ON_ERROR);

    expect($response->getStatusCode())->toBe(200)
        ->and($payload['email'])->toBe('ada@nuvora.test')
        ->and($payload['role'])->toBe('customer')
        ->and($payload)->toHaveKey('id')
        ->and($payload)->not->toHaveKey('accessToken')
        ->and((string) $response->getContent())->not->toContain($token)
        ->and((string) $response->getContent())->not->toContain('secret-secret');
});

it('rejects a missing bearer token', function () {
    $this->client->jsonRequest('GET', '/auth/me');

    $response = $this->client->getResponse();
    $payload = json_decode((string) $response->getContent(), true, flags: JSON_THROW_ON_ERROR);

    expect($response->getStatusCode())->toBe(401)
        ->and($payload['error'])->toBe([
            'code' => 'unauthenticated',
            'message' => 'The request is not authenticated.',
        ]);
});

it('rejects an invalid bearer token', function () {
    $this->client->jsonRequest('GET', '/auth/me', [], [
        'HTTP_AUTHORIZATION' => 'Bearer '.str_repeat('a', 64),
    ]);

    $response = $this->client->getResponse();
    $payload = json_decode((string) $response->getContent(), true, flags: JSON_THROW_ON_ERROR);

    expect($response->getStatusCode())->toBe(401)
        ->and($payload['error']['code'])->toBe('unauthenticated')
        ->and((string) $response->getContent())->not->toContain(str_repeat('a', 64));
});
