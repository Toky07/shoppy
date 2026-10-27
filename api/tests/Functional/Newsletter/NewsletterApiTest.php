<?php

declare(strict_types=1);

it('subscribes an email to the newsletter', function () {
    $this->client->jsonRequest('POST', '/newsletter', [
        'email' => 'Ada@Shoppy.test',
    ]);

    $response = $this->client->getResponse();
    $payload = json_decode((string) $response->getContent(), true, flags: JSON_THROW_ON_ERROR);

    expect($response->getStatusCode())->toBe(201)
        ->and($payload)->toBe(['status' => 'subscribed'])
        ->and((string) $response->getContent())->not->toContain('ada@shoppy.test');
});

it('accepts a second subscribe of the same email', function () {
    $this->client->jsonRequest('POST', '/newsletter', ['email' => 'ada@shoppy.test']);
    $this->client->jsonRequest('POST', '/newsletter', ['email' => 'ada@shoppy.test']);

    $response = $this->client->getResponse();
    $payload = json_decode((string) $response->getContent(), true, flags: JSON_THROW_ON_ERROR);

    expect($response->getStatusCode())->toBe(201)
        ->and($payload)->toBe(['status' => 'subscribed']);
});

it('rejects a missing email', function () {
    $this->client->jsonRequest('POST', '/newsletter', []);

    $response = $this->client->getResponse();
    $payload = json_decode((string) $response->getContent(), true, flags: JSON_THROW_ON_ERROR);

    expect($response->getStatusCode())->toBe(400)
        ->and($payload['error']['code'])->toBe('validation_error')
        ->and($payload['error']['violations'])->toBe([
            ['field' => 'email', 'message' => 'This field is required.'],
        ]);
});

it('rejects an invalid email', function () {
    $this->client->jsonRequest('POST', '/newsletter', [
        'email' => 'not-an-email',
    ]);

    $response = $this->client->getResponse();
    $payload = json_decode((string) $response->getContent(), true, flags: JSON_THROW_ON_ERROR);

    expect($response->getStatusCode())->toBe(400)
        ->and($payload['error']['code'])->toBe('validation_error')
        ->and($payload['error']['violations'][0]['field'])->toBe('email');
});
