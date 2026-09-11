<?php

declare(strict_types=1);

function cartProduct(string $name = 'Nuvora Tee', int $priceCents = 1999, int $stock = 10): array
{
    test()->client->jsonRequest('POST', '/products', [
        'name' => $name,
        'priceCents' => $priceCents,
        'stock' => $stock,
    ], catalogAdminHeaders());

    return json_decode((string) test()->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);
}

it('returns an empty cart for a new customer', function () {
    $this->client->jsonRequest('GET', '/cart', [], catalogCustomerHeaders());

    $response = $this->client->getResponse();
    $payload = json_decode((string) $response->getContent(), true, flags: JSON_THROW_ON_ERROR);

    expect($response->getStatusCode())->toBe(200)
        ->and($payload['id'])->toBeNull()
        ->and($payload['items'])->toBe([])
        ->and($payload['total'])->toBe(['cents' => 0, 'currency' => 'EUR']);
});

it('adds an item to the cart', function () {
    $product = cartProduct();

    $this->client->jsonRequest('POST', '/cart/items', [
        'productId' => $product['id'],
        'quantity' => 2,
    ], catalogCustomerHeaders());

    $response = $this->client->getResponse();
    $payload = json_decode((string) $response->getContent(), true, flags: JSON_THROW_ON_ERROR);

    expect($response->getStatusCode())->toBe(200)
        ->and($payload['items'])->toHaveCount(1)
        ->and($payload['items'][0]['productId'])->toBe($product['id'])
        ->and($payload['items'][0]['name'])->toBe('Nuvora Tee')
        ->and($payload['items'][0]['quantity'])->toBe(2)
        ->and($payload['items'][0]['availableStock'])->toBe(10)
        ->and($payload['total'])->toBe(['cents' => 3998, 'currency' => 'EUR']);
});

it('updates removes and clears cart items', function () {
    $product = cartProduct(stock: 20);

    $this->client->jsonRequest('POST', '/cart/items', [
        'productId' => $product['id'],
        'quantity' => 2,
    ], catalogCustomerHeaders());

    $this->client->jsonRequest('PUT', '/cart/items/'.$product['id'], [
        'quantity' => 5,
    ], catalogCustomerHeaders());
    $updated = json_decode((string) $this->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);
    expect($updated['items'][0]['quantity'])->toBe(5);

    $this->client->jsonRequest('DELETE', '/cart/items/'.$product['id'], [], catalogCustomerHeaders());
    $removed = json_decode((string) $this->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);
    expect($removed['items'])->toBe([]);

    $this->client->jsonRequest('POST', '/cart/items', [
        'productId' => $product['id'],
        'quantity' => 1,
    ], catalogCustomerHeaders());
    $this->client->jsonRequest('DELETE', '/cart', [], catalogCustomerHeaders());
    $cleared = json_decode((string) $this->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);
    expect($cleared['items'])->toBe([]);
});

it('checks out the cart into an order and empties the cart', function () {
    $product = cartProduct(stock: 5);

    $this->client->jsonRequest('POST', '/cart/items', [
        'productId' => $product['id'],
        'quantity' => 2,
    ], catalogCustomerHeaders());

    $this->client->jsonRequest('POST', '/cart/checkout', [], catalogCustomerHeaders());

    $response = $this->client->getResponse();
    $order = json_decode((string) $response->getContent(), true, flags: JSON_THROW_ON_ERROR);

    expect($response->getStatusCode())->toBe(201)
        ->and($order['status'])->toBe('pending')
        ->and($order['items'][0]['quantity'])->toBe(2)
        ->and($order['total'])->toBe(['cents' => 3998, 'currency' => 'EUR'])
        ->and($response->headers->get('Location'))->toBe('/orders/'.$order['id']);

    $this->client->jsonRequest('GET', '/cart', [], catalogCustomerHeaders());
    $cart = json_decode((string) $this->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);
    expect($cart['items'])->toBe([]);

    $this->client->jsonRequest('GET', '/products/'.$product['id']);
    $fetched = json_decode((string) $this->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);
    expect($fetched['stock'])->toBe(3);
});

it('rejects unauthenticated cart access', function () {
    $this->client->jsonRequest('GET', '/cart');

    $response = $this->client->getResponse();
    $payload = json_decode((string) $response->getContent(), true, flags: JSON_THROW_ON_ERROR);

    expect($response->getStatusCode())->toBe(401)
        ->and($payload['error']['code'])->toBe('unauthenticated');
});

it('rejects adding more than available stock', function () {
    $product = cartProduct(stock: 1);

    $this->client->jsonRequest('POST', '/cart/items', [
        'productId' => $product['id'],
        'quantity' => 2,
    ], catalogCustomerHeaders());

    $response = $this->client->getResponse();
    $payload = json_decode((string) $response->getContent(), true, flags: JSON_THROW_ON_ERROR);

    expect($response->getStatusCode())->toBe(409)
        ->and($payload['error']['code'])->toBe('insufficient_product_stock');
});

it('rejects checking out an empty cart', function () {
    $this->client->jsonRequest('POST', '/cart/checkout', [], catalogCustomerHeaders());

    $response = $this->client->getResponse();
    $payload = json_decode((string) $response->getContent(), true, flags: JSON_THROW_ON_ERROR);

    expect($response->getStatusCode())->toBe(400)
        ->and($payload['error']['code'])->toBe('validation_error');
});
