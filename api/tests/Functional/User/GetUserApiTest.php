<?php

declare(strict_types=1);

function getUserLogin(string $email): array
{
    $user = registerCatalogUser(test()->client, $email);

    test()->client->jsonRequest('POST', '/auth/login', [
        'email' => $email,
        'password' => 'secret-secret',
    ]);

    $login = json_decode((string) test()->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);

    return [
        'user' => $user,
        'headers' => ['HTTP_AUTHORIZATION' => 'Bearer '.$login['accessToken']],
    ];
}

it('returns a user to themselves', function () {
    $account = getUserLogin('ada@nuvora.test');

    $this->client->jsonRequest('GET', '/users/'.$account['user']['id'], [], $account['headers']);

    $response = $this->client->getResponse();
    $payload = json_decode((string) $response->getContent(), true, flags: JSON_THROW_ON_ERROR);

    expect($response->getStatusCode())->toBe(200)
        ->and($payload['id'])->toBe($account['user']['id'])
        ->and($payload['email'])->toBe('ada@nuvora.test')
        ->and($payload['role'])->toBe('customer')
        ->and($payload)->toHaveKey('createdAt')
        ->and($payload)->not->toHaveKey('password');
});

it('lets an admin read another user', function () {
    $target = registerCatalogUser($this->client, 'ada@nuvora.test');

    $this->client->jsonRequest('GET', '/users/'.$target['id'], [], catalogAdminHeaders());

    $response = $this->client->getResponse();
    $payload = json_decode((string) $response->getContent(), true, flags: JSON_THROW_ON_ERROR);

    expect($response->getStatusCode())->toBe(200)
        ->and($payload['id'])->toBe($target['id'])
        ->and($payload['email'])->toBe('ada@nuvora.test');
});

it('rejects a customer reading another user', function () {
    $target = registerCatalogUser($this->client, 'ada@nuvora.test');
    $intruder = getUserLogin('intruder@nuvora.test');

    $this->client->jsonRequest('GET', '/users/'.$target['id'], [], $intruder['headers']);

    $response = $this->client->getResponse();
    $payload = json_decode((string) $response->getContent(), true, flags: JSON_THROW_ON_ERROR);

    expect($response->getStatusCode())->toBe(403)
        ->and($payload['error']['code'])->toBe('forbidden')
        ->and((string) $response->getContent())->not->toContain('ada@nuvora.test');
});

it('rejects unauthenticated reads', function () {
    $target = registerCatalogUser($this->client, 'ada@nuvora.test');

    $this->client->jsonRequest('GET', '/users/'.$target['id']);

    $response = $this->client->getResponse();
    $payload = json_decode((string) $response->getContent(), true, flags: JSON_THROW_ON_ERROR);

    expect($response->getStatusCode())->toBe(401)
        ->and($payload['error']['code'])->toBe('unauthenticated');
});

it('returns not found for an unknown user when an admin asks', function () {
    $this->client->jsonRequest(
        'GET',
        '/users/550e8400-e29b-41d4-a716-446655440000',
        [],
        catalogAdminHeaders(),
    );

    $response = $this->client->getResponse();
    $payload = json_decode((string) $response->getContent(), true, flags: JSON_THROW_ON_ERROR);

    expect($response->getStatusCode())->toBe(404)
        ->and($payload['error']['code'])->toBe('user_not_found');
});

it('follows the registration Location header', function () {
    $this->client->jsonRequest('POST', '/users', [
        'email' => 'Ada@Nuvora.test',
        'password' => 'secret-secret',
    ]);

    $created = $this->client->getResponse();
    $payload = json_decode((string) $created->getContent(), true, flags: JSON_THROW_ON_ERROR);
    $location = $created->headers->get('Location');

    $this->client->jsonRequest('POST', '/auth/login', [
        'email' => 'ada@nuvora.test',
        'password' => 'secret-secret',
    ]);
    $login = json_decode((string) $this->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);

    $this->client->jsonRequest('GET', $location, [], [
        'HTTP_AUTHORIZATION' => 'Bearer '.$login['accessToken'],
    ]);

    $response = $this->client->getResponse();
    $user = json_decode((string) $response->getContent(), true, flags: JSON_THROW_ON_ERROR);

    expect($location)->toBe('/users/'.$payload['id'])
        ->and($response->getStatusCode())->toBe(200)
        ->and($user['id'])->toBe($payload['id'])
        ->and($user['email'])->toBe('ada@nuvora.test');
});
