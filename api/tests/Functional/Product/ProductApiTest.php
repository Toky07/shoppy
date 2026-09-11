<?php

declare(strict_types=1);

it('creates a product', function () {
    $this->client->jsonRequest('POST', '/products', [
        'name' => 'Nuvora Tee',
        'priceCents' => 1999,
        'description' => 'Soft cotton t-shirt',
    ], catalogAdminHeaders());

    $response = $this->client->getResponse();
    $payload = json_decode((string) $response->getContent(), true, flags: JSON_THROW_ON_ERROR);

    expect($response->getStatusCode())->toBe(201)
        ->and($payload['name'])->toBe('Nuvora Tee')
        ->and($payload['description'])->toBe('Soft cotton t-shirt')
        ->and($payload['price'])->toBe(['cents' => 1999, 'currency' => 'EUR'])
        ->and($payload['stock'])->toBe(0)
        ->and($payload['imageUrl'])->toBeNull()
        ->and($response->headers->get('Location'))->toBe('/products/'.$payload['id']);
});

it('creates a product with an image', function () {
    $this->client->jsonRequest('POST', '/products', [
        'name' => 'Nuvora Tee',
        'priceCents' => 1999,
        'imageUrl' => '/media/products/nuvora-tee.svg',
    ], catalogAdminHeaders());

    $payload = json_decode((string) $this->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);

    expect($this->client->getResponse()->getStatusCode())->toBe(201)
        ->and($payload['imageUrl'])->toBe('/media/products/nuvora-tee.svg');
});

it('rejects an invalid product payload', function () {
    $this->client->jsonRequest('POST', '/products', [
        'priceCents' => 1999,
    ], catalogAdminHeaders());

    $response = $this->client->getResponse();
    $payload = json_decode((string) $response->getContent(), true, flags: JSON_THROW_ON_ERROR);

    expect($response->getStatusCode())->toBe(400)
        ->and($payload['error']['code'])->toBe('validation_error')
        ->and($payload['error']['message'])->toBe('The request is invalid.')
        ->and($payload['error']['violations'])->toBe([
            ['field' => 'name', 'message' => 'This field is required.'],
        ]);
});

it('rejects a domain validation error with the same format', function () {
    $this->client->jsonRequest('POST', '/products', [
        'name' => '',
        'priceCents' => 1999,
    ], catalogAdminHeaders());

    $response = $this->client->getResponse();
    $payload = json_decode((string) $response->getContent(), true, flags: JSON_THROW_ON_ERROR);

    expect($response->getStatusCode())->toBe(400)
        ->and($payload['error']['code'])->toBe('validation_error')
        ->and($payload['error']['violations'])->toBe([
            ['field' => 'name', 'message' => 'Product name cannot be empty.'],
        ]);
});

it('rejects invalid json without leaking parser details', function () {
    $this->client->request('POST', '/products', [], [], [
        'CONTENT_TYPE' => 'application/json',
        'HTTP_ACCEPT' => 'application/json',
        ...catalogAdminHeaders(),
    ], '{not json');

    $response = $this->client->getResponse();
    $payload = json_decode((string) $response->getContent(), true, flags: JSON_THROW_ON_ERROR);

    expect($response->getStatusCode())->toBe(400)
        ->and($payload['error'])->toBe([
            'code' => 'invalid_json',
            'message' => 'The request body is not valid JSON.',
        ])
        ->and((string) $response->getContent())->not->toContain('Syntax error')
        ->and((string) $response->getContent())->not->toContain('Could not decode');
});

it('gets a created product', function () {
    $this->client->jsonRequest('POST', '/products', [
        'name' => 'Nuvora Tee',
        'priceCents' => 1999,
    ], catalogAdminHeaders());

    $created = json_decode((string) $this->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);

    $this->client->jsonRequest('GET', '/products/'.$created['id']);
    $response = $this->client->getResponse();
    $payload = json_decode((string) $response->getContent(), true, flags: JSON_THROW_ON_ERROR);

    expect($response->getStatusCode())->toBe(200)
        ->and($payload['id'])->toBe($created['id'])
        ->and($payload['name'])->toBe('Nuvora Tee')
        ->and($payload['description'])->toBeNull();
});

it('returns not found for an unknown product', function () {
    $this->client->jsonRequest('GET', '/products/550e8400-e29b-41d4-a716-446655440000');

    $response = $this->client->getResponse();
    $payload = json_decode((string) $response->getContent(), true, flags: JSON_THROW_ON_ERROR);

    expect($response->getStatusCode())->toBe(404)
        ->and($payload['error'])->toBe([
            'code' => 'product_not_found',
            'message' => 'The requested resource was not found.',
        ])
        ->and((string) $response->getContent())->not->toContain('550e8400');
});

it('lists an empty catalog', function () {
    $this->client->jsonRequest('GET', '/products');

    $response = $this->client->getResponse();
    $payload = json_decode((string) $response->getContent(), true, flags: JSON_THROW_ON_ERROR);

    expect($response->getStatusCode())->toBe(200)
        ->and($payload)->toBe([
            'items' => [],
            'page' => 1,
            'limit' => 20,
            'total' => 0,
        ]);
});

it('lists created products', function () {
    $this->client->jsonRequest('POST', '/products', [
        'name' => 'Nuvora Tee',
        'priceCents' => 1999,
    ], catalogAdminHeaders());
    $this->client->jsonRequest('POST', '/products', [
        'name' => 'Nuvora Hoodie',
        'priceCents' => 4999,
    ], catalogAdminHeaders());

    $this->client->jsonRequest('GET', '/products');
    $response = $this->client->getResponse();
    $payload = json_decode((string) $response->getContent(), true, flags: JSON_THROW_ON_ERROR);

    expect($response->getStatusCode())->toBe(200)
        ->and($payload['total'])->toBe(2)
        ->and($payload['items'])->toHaveCount(2)
        ->and(array_column($payload['items'], 'name'))->toContain('Nuvora Tee', 'Nuvora Hoodie');
});

it('rejects an invalid list page', function () {
    $this->client->jsonRequest('GET', '/products?page=0');

    $response = $this->client->getResponse();
    $payload = json_decode((string) $response->getContent(), true, flags: JSON_THROW_ON_ERROR);

    expect($response->getStatusCode())->toBe(400)
        ->and($payload['error']['code'])->toBe('validation_error')
        ->and($payload['error']['violations'])->toBe([
            ['field' => 'page', 'message' => 'Product list page and limit must be valid.'],
        ]);
});

it('searches listed products', function () {
    $this->client->jsonRequest('POST', '/products', [
        'name' => 'Nuvora Tee',
        'priceCents' => 1999,
        'description' => 'Soft cotton t-shirt',
    ], catalogAdminHeaders());
    $this->client->jsonRequest('POST', '/products', [
        'name' => 'Nuvora Hoodie',
        'priceCents' => 4999,
    ], catalogAdminHeaders());

    $this->client->jsonRequest('GET', '/products?q=hoodie');
    $payload = json_decode((string) $this->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);

    expect($this->client->getResponse()->getStatusCode())->toBe(200)
        ->and($payload['total'])->toBe(1)
        ->and(array_column($payload['items'], 'name'))->toBe(['Nuvora Hoodie']);
});

it('sorts listed products by price', function () {
    $this->client->jsonRequest('POST', '/products', [
        'name' => 'Nuvora Hoodie',
        'priceCents' => 4999,
    ], catalogAdminHeaders());
    $this->client->jsonRequest('POST', '/products', [
        'name' => 'Nuvora Tee',
        'priceCents' => 1999,
    ], catalogAdminHeaders());

    $this->client->jsonRequest('GET', '/products?sort=price_asc');
    $payload = json_decode((string) $this->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);

    expect($this->client->getResponse()->getStatusCode())->toBe(200)
        ->and(array_column($payload['items'], 'name'))->toBe(['Nuvora Tee', 'Nuvora Hoodie']);
});

it('rejects an invalid list sort', function () {
    $this->client->jsonRequest('GET', '/products?sort=popularity');

    $response = $this->client->getResponse();
    $payload = json_decode((string) $response->getContent(), true, flags: JSON_THROW_ON_ERROR);

    expect($response->getStatusCode())->toBe(400)
        ->and($payload['error']['code'])->toBe('validation_error')
        ->and($payload['error']['violations'])->toBe([
            ['field' => 'sort', 'message' => 'Product list sort must be valid.'],
        ]);
});

it('updates a product', function () {
    $this->client->jsonRequest('POST', '/products', [
        'name' => 'Nuvora Tee',
        'priceCents' => 1999,
        'description' => 'Soft cotton t-shirt',
    ], catalogAdminHeaders());
    $created = json_decode((string) $this->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);

    $this->client->jsonRequest('PATCH', '/products/'.$created['id'], [
        'name' => 'Nuvora Hoodie',
        'priceCents' => 4999,
    ], catalogAdminHeaders());
    $response = $this->client->getResponse();
    $payload = json_decode((string) $response->getContent(), true, flags: JSON_THROW_ON_ERROR);

    expect($response->getStatusCode())->toBe(200)
        ->and($payload['name'])->toBe('Nuvora Hoodie')
        ->and($payload['price']['cents'])->toBe(4999)
        ->and($payload['description'])->toBe('Soft cotton t-shirt');
});

it('clears a product description', function () {
    $this->client->jsonRequest('POST', '/products', [
        'name' => 'Nuvora Tee',
        'priceCents' => 1999,
        'description' => 'Soft cotton t-shirt',
    ], catalogAdminHeaders());
    $created = json_decode((string) $this->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);

    $this->client->jsonRequest('PATCH', '/products/'.$created['id'], [
        'description' => null,
    ], catalogAdminHeaders());
    $payload = json_decode((string) $this->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);

    expect($this->client->getResponse()->getStatusCode())->toBe(200)
        ->and($payload['description'])->toBeNull();
});

it('rejects an empty product update', function () {
    $this->client->jsonRequest('POST', '/products', [
        'name' => 'Nuvora Tee',
        'priceCents' => 1999,
    ], catalogAdminHeaders());
    $created = json_decode((string) $this->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);

    $this->client->jsonRequest('PATCH', '/products/'.$created['id'], [], catalogAdminHeaders());
    $payload = json_decode((string) $this->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);

    expect($this->client->getResponse()->getStatusCode())->toBe(400)
        ->and($payload['error']['code'])->toBe('validation_error')
        ->and($payload['error']['violations'])->toBe([
            ['message' => 'At least one field is required.'],
        ]);
});

it('deletes a product', function () {
    $this->client->jsonRequest('POST', '/products', [
        'name' => 'Nuvora Tee',
        'priceCents' => 1999,
    ], catalogAdminHeaders());
    $created = json_decode((string) $this->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);

    $this->client->jsonRequest('DELETE', '/products/'.$created['id'], [], catalogAdminHeaders());

    expect($this->client->getResponse()->getStatusCode())->toBe(204)
        ->and($this->client->getResponse()->getContent())->toBe('');

    $this->client->jsonRequest('GET', '/products/'.$created['id']);
    $payload = json_decode((string) $this->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);

    expect($this->client->getResponse()->getStatusCode())->toBe(404)
        ->and($payload['error']['code'])->toBe('product_not_found')
        ->and((string) $this->client->getResponse()->getContent())->not->toContain($created['id']);
});

it('returns not found when deleting an unknown product', function () {
    $this->client->jsonRequest('DELETE', '/products/550e8400-e29b-41d4-a716-446655440000', [], catalogAdminHeaders());
    $payload = json_decode((string) $this->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);

    expect($this->client->getResponse()->getStatusCode())->toBe(404)
        ->and($payload['error'])->toBe([
            'code' => 'product_not_found',
            'message' => 'The requested resource was not found.',
        ])
        ->and((string) $this->client->getResponse()->getContent())->not->toContain('550e8400');
});

it('returns not found for an unknown route without leaking the path', function () {
    $this->client->jsonRequest('GET', '/admin/secret');
    $payload = json_decode((string) $this->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);

    expect($this->client->getResponse()->getStatusCode())->toBe(404)
        ->and($payload['error'])->toBe([
            'code' => 'not_found',
            'message' => 'The requested resource was not found.',
        ])
        ->and((string) $this->client->getResponse()->getContent())->not->toContain('/admin');
});

it('rejects unauthenticated catalog writes', function (string $method, string $uri) {
    $this->client->jsonRequest($method, $uri, [
        'name' => 'Nuvora Tee',
        'priceCents' => 1999,
    ]);

    $response = $this->client->getResponse();
    $payload = json_decode((string) $response->getContent(), true, flags: JSON_THROW_ON_ERROR);

    expect($response->getStatusCode())->toBe(401)
        ->and($payload['error'])->toBe([
            'code' => 'unauthenticated',
            'message' => 'The request is not authenticated.',
        ]);
})->with([
    ['POST', '/products'],
    ['PATCH', '/products/550e8400-e29b-41d4-a716-446655440000'],
    ['DELETE', '/products/550e8400-e29b-41d4-a716-446655440000'],
]);

it('rejects a customer creating a product', function () {
    $this->client->jsonRequest('POST', '/products', [
        'name' => 'Nuvora Tee',
        'priceCents' => 1999,
    ], catalogCustomerHeaders());

    $response = $this->client->getResponse();
    $payload = json_decode((string) $response->getContent(), true, flags: JSON_THROW_ON_ERROR);

    expect($response->getStatusCode())->toBe(403)
        ->and($payload['error'])->toBe([
            'code' => 'forbidden',
            'message' => 'The request is not authorized.',
        ])
        ->and((string) $response->getContent())->not->toContain('customer@nuvora.test');

    $this->client->jsonRequest('GET', '/products');
    $list = json_decode((string) $this->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);

    expect($list['total'])->toBe(0);
});

it('rejects a customer updating a product', function () {
    $this->client->jsonRequest('POST', '/products', [
        'name' => 'Nuvora Tee',
        'priceCents' => 1999,
    ], catalogAdminHeaders());
    $created = json_decode((string) $this->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);

    $this->client->jsonRequest('PATCH', '/products/'.$created['id'], [
        'name' => 'Hijacked Tee',
    ], catalogCustomerHeaders());

    $response = $this->client->getResponse();
    $payload = json_decode((string) $response->getContent(), true, flags: JSON_THROW_ON_ERROR);

    expect($response->getStatusCode())->toBe(403)
        ->and($payload['error']['code'])->toBe('forbidden');

    $this->client->jsonRequest('GET', '/products/'.$created['id']);
    $product = json_decode((string) $this->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);

    expect($product['name'])->toBe('Nuvora Tee');
});

it('rejects a customer deleting a product', function () {
    $this->client->jsonRequest('POST', '/products', [
        'name' => 'Nuvora Tee',
        'priceCents' => 1999,
    ], catalogAdminHeaders());
    $created = json_decode((string) $this->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);

    $this->client->jsonRequest('DELETE', '/products/'.$created['id'], [], catalogCustomerHeaders());

    $response = $this->client->getResponse();
    $payload = json_decode((string) $response->getContent(), true, flags: JSON_THROW_ON_ERROR);

    expect($response->getStatusCode())->toBe(403)
        ->and($payload['error']['code'])->toBe('forbidden');

    $this->client->jsonRequest('GET', '/products/'.$created['id']);

    expect($this->client->getResponse()->getStatusCode())->toBe(200);
});

it('lists products without authentication', function () {
    $this->client->jsonRequest('POST', '/products', [
        'name' => 'Nuvora Tee',
        'priceCents' => 1999,
    ], catalogAdminHeaders());

    $this->client->jsonRequest('GET', '/products');
    $payload = json_decode((string) $this->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);

    expect($this->client->getResponse()->getStatusCode())->toBe(200)
        ->and($payload['total'])->toBe(1)
        ->and($payload['items'][0]['name'])->toBe('Nuvora Tee');
});
