<?php

declare(strict_types=1);

use Symfony\Bundle\FrameworkBundle\KernelBrowser;

function logoutLoginAccessToken(KernelBrowser $client): string
{
    $client->jsonRequest('POST', '/users', [
        'email' => 'Ada@Nuvora.test',
        'password' => 'secret-secret',
    ]);
    $client->jsonRequest('POST', '/auth/login', [
        'email' => 'ada@nuvora.test',
        'password' => 'secret-secret',
    ]);

    return sessionAccessToken($client);
}

it('logs out and revokes the current access token', function () {
    $token = logoutLoginAccessToken($this->client);

    $this->client->jsonRequest('POST', '/auth/logout', [], [
        'HTTP_AUTHORIZATION' => 'Bearer '.$token,
    ]);

    $logoutResponse = $this->client->getResponse();

    expect($logoutResponse->getStatusCode())->toBe(204)
        ->and($logoutResponse->getContent())->toBe('');

    $this->client->jsonRequest('GET', '/auth/me', [], [
        'HTTP_AUTHORIZATION' => 'Bearer '.$token,
    ]);

    $meResponse = $this->client->getResponse();
    $payload = json_decode((string) $meResponse->getContent(), true, flags: JSON_THROW_ON_ERROR);

    expect($meResponse->getStatusCode())->toBe(401)
        ->and($payload['error']['code'])->toBe('unauthenticated')
        ->and((string) $meResponse->getContent())->not->toContain($token);
});

it('rejects a missing bearer token', function () {
    $this->client->jsonRequest('POST', '/auth/logout');

    $response = $this->client->getResponse();
    $payload = json_decode((string) $response->getContent(), true, flags: JSON_THROW_ON_ERROR);

    expect($response->getStatusCode())->toBe(401)
        ->and($payload['error'])->toBe([
            'code' => 'unauthenticated',
            'message' => 'The request is not authenticated.',
        ]);
});

it('rejects an invalid bearer token', function () {
    $this->client->jsonRequest('POST', '/auth/logout', [], [
        'HTTP_AUTHORIZATION' => 'Bearer '.str_repeat('a', 64),
    ]);

    $response = $this->client->getResponse();
    $payload = json_decode((string) $response->getContent(), true, flags: JSON_THROW_ON_ERROR);

    expect($response->getStatusCode())->toBe(401)
        ->and($payload['error']['code'])->toBe('unauthenticated')
        ->and((string) $response->getContent())->not->toContain(str_repeat('a', 64));
});

it('rejects a second logout with the same token', function () {
    $token = logoutLoginAccessToken($this->client);

    $this->client->jsonRequest('POST', '/auth/logout', [], [
        'HTTP_AUTHORIZATION' => 'Bearer '.$token,
    ]);

    expect($this->client->getResponse()->getStatusCode())->toBe(204);

    $this->client->jsonRequest('POST', '/auth/logout', [], [
        'HTTP_AUTHORIZATION' => 'Bearer '.$token,
    ]);

    $response = $this->client->getResponse();
    $payload = json_decode((string) $response->getContent(), true, flags: JSON_THROW_ON_ERROR);

    expect($response->getStatusCode())->toBe(401)
        ->and($payload['error']['code'])->toBe('unauthenticated');
});
