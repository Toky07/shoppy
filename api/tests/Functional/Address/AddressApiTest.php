<?php

declare(strict_types=1);

use App\User\Domain\ValueObject\Role;

function addressProduct(string $name = 'Nuvora Tee'): array
{
    test()->client->jsonRequest('POST', '/products', [
        'name' => $name,
        'priceCents' => 1999,
        'stock' => 10,
    ], catalogAdminHeaders());

    return json_decode((string) test()->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);
}

function checkoutAddress(array $headers, array $shipping = []): void
{
    $product = addressProduct();
    test()->client->jsonRequest('POST', '/cart/items', [
        'productId' => $product['id'],
        'quantity' => 1,
    ], $headers);
    test()->client->jsonRequest('POST', '/cart/checkout', [
        ...deliveryFields(),
        ...($shipping === [] ? [] : ['shippingAddress' => $shipping]),
    ], $headers);
}

function parisAddressPayload(): array
{
    return [
        'recipient' => 'Ada Lovelace',
        'line1' => '10 rue de la Paix',
        'line2' => null,
        'postalCode' => '75002',
        'city' => 'Paris',
        'country' => 'FR',
    ];
}

function lyonAddressPayload(): array
{
    return [
        'recipient' => 'Ada Lovelace',
        'line1' => '2 place Bellecour',
        'line2' => null,
        'postalCode' => '69002',
        'city' => 'Lyon',
        'country' => 'FR',
    ];
}

it('returns an empty address book for a new customer', function () {
    $this->client->jsonRequest('GET', '/addresses', [], catalogCustomerHeaders());

    $response = $this->client->getResponse();
    $payload = json_decode((string) $response->getContent(), true, flags: JSON_THROW_ON_ERROR);

    expect($response->getStatusCode())->toBe(200)
        ->and($payload)->toBe(['addresses' => []]);
});

it('saves the checkout address on the account', function () {
    $headers = catalogCustomerHeaders();
    checkoutAddress($headers);

    $this->client->jsonRequest('GET', '/addresses', [], $headers);
    $payload = json_decode((string) $this->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);

    expect($this->client->getResponse()->getStatusCode())->toBe(200)
        ->and($payload['addresses'])->toHaveCount(1)
        ->and($payload['addresses'][0])->toMatchArray([...parisAddressPayload(), 'isDefault' => true])
        ->and($payload['addresses'][0]['id'])->toBeString();
});

it('does not duplicate the same checkout address', function () {
    $headers = catalogCustomerHeaders();
    checkoutAddress($headers);
    checkoutAddress($headers);

    $this->client->jsonRequest('GET', '/addresses', [], $headers);
    $payload = json_decode((string) $this->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);

    expect($payload['addresses'])->toHaveCount(1)
        ->and($payload['addresses'][0]['city'])->toBe('Paris');
});

it('prepends a new checkout address and makes it the default', function () {
    $headers = catalogCustomerHeaders();
    checkoutAddress($headers);
    checkoutAddress($headers, lyonAddressPayload());

    $this->client->jsonRequest('GET', '/addresses', [], $headers);
    $payload = json_decode((string) $this->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);

    expect($payload['addresses'])->toHaveCount(2)
        ->and($payload['addresses'][0])->toMatchArray([...lyonAddressPayload(), 'isDefault' => true])
        ->and($payload['addresses'][1])->toMatchArray([...parisAddressPayload(), 'isDefault' => false]);
});

it('removes and marks a saved address as default', function () {
    $headers = catalogCustomerHeaders();
    checkoutAddress($headers);
    checkoutAddress($headers, lyonAddressPayload());

    $this->client->jsonRequest('GET', '/addresses', [], $headers);
    $book = json_decode((string) $this->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);
    $lyonId = $book['addresses'][0]['id'];
    $parisId = $book['addresses'][1]['id'];

    $this->client->jsonRequest('POST', '/addresses/'.$parisId.'/default', [], $headers);
    $marked = json_decode((string) $this->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);
    expect($marked['addresses'][1]['isDefault'])->toBeTrue()
        ->and($marked['addresses'][0]['isDefault'])->toBeFalse();

    $this->client->jsonRequest('DELETE', '/addresses/'.$lyonId, [], $headers);
    $removed = json_decode((string) $this->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);
    expect($removed['addresses'])->toHaveCount(1)
        ->and($removed['addresses'][0]['id'])->toBe($parisId)
        ->and($removed['addresses'][0]['isDefault'])->toBeTrue();
});

it('saves the guest checkout address on the opened session', function () {
    $product = addressProduct();

    $this->client->jsonRequest('POST', '/cart/checkout', [
        'email' => 'guest-address@nuvora.test',
        'items' => [
            ['productId' => $product['id'], 'quantity' => 1],
        ],
        ...deliveryFields(),
    ]);

    expect($this->client->getResponse()->getStatusCode())->toBe(201);

    $this->client->jsonRequest('GET', '/addresses');
    $payload = json_decode((string) $this->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);

    expect($this->client->getResponse()->getStatusCode())->toBe(200)
        ->and($payload['addresses'])->toHaveCount(1)
        ->and($payload['addresses'][0])->toMatchArray([...parisAddressPayload(), 'isDefault' => true]);
});

it('keeps address books private', function () {
    $this->client->jsonRequest('GET', '/addresses');
    expect($this->client->getResponse()->getStatusCode())->toBe(401);

    $headers = catalogCustomerHeaders();
    checkoutAddress($headers);

    $this->client->jsonRequest('GET', '/addresses', [], catalogAuthHeaders('other-address@nuvora.test', Role::customer()));
    $other = json_decode((string) $this->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);
    expect($other['addresses'])->toBe([]);
});
