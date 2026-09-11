<?php

declare(strict_types=1);

it('sets product stock for an admin', function () {
    $this->client->jsonRequest('POST', '/products', [
        'name' => 'Nuvora Tee',
        'priceCents' => 1999,
        'stock' => 3,
    ], catalogAdminHeaders());

    $created = json_decode((string) $this->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);

    expect($created['stock'])->toBe(3);

    $this->client->jsonRequest('PUT', '/products/'.$created['id'].'/stock', [
        'stock' => 20,
    ], catalogAdminHeaders());

    $response = $this->client->getResponse();
    $payload = json_decode((string) $response->getContent(), true, flags: JSON_THROW_ON_ERROR);

    expect($response->getStatusCode())->toBe(200)
        ->and($payload['stock'])->toBe(20);

    $this->client->jsonRequest('GET', '/products/'.$created['id']);
    $fetched = json_decode((string) $this->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);

    expect($fetched['stock'])->toBe(20);
});

it('rejects a customer setting stock', function () {
    $this->client->jsonRequest('POST', '/products', [
        'name' => 'Nuvora Tee',
        'priceCents' => 1999,
    ], catalogAdminHeaders());
    $created = json_decode((string) $this->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);

    $this->client->jsonRequest('PUT', '/products/'.$created['id'].'/stock', [
        'stock' => 20,
    ], catalogCustomerHeaders());

    $response = $this->client->getResponse();
    $payload = json_decode((string) $response->getContent(), true, flags: JSON_THROW_ON_ERROR);

    expect($response->getStatusCode())->toBe(403)
        ->and($payload['error']['code'])->toBe('forbidden');
});

it('rejects negative stock values', function () {
    $this->client->jsonRequest('POST', '/products', [
        'name' => 'Nuvora Tee',
        'priceCents' => 1999,
    ], catalogAdminHeaders());
    $created = json_decode((string) $this->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);

    $this->client->jsonRequest('PUT', '/products/'.$created['id'].'/stock', [
        'stock' => -1,
    ], catalogAdminHeaders());

    $response = $this->client->getResponse();
    $payload = json_decode((string) $response->getContent(), true, flags: JSON_THROW_ON_ERROR);

    expect($response->getStatusCode())->toBe(400)
        ->and($payload['error']['code'])->toBe('validation_error')
        ->and($payload['error']['violations'])->toBe([
            ['field' => 'stock', 'message' => 'Product stock cannot be negative.'],
        ]);
});
