<?php

declare(strict_types=1);

use App\User\Domain\ValueObject\Role;

function favoriteProduct(string $name = 'Nuvora Tee'): array
{
    test()->client->jsonRequest('POST', '/products', [
        'name' => $name,
        'priceCents' => 1999,
        'stock' => 10,
    ], catalogAdminHeaders());

    return json_decode((string) test()->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);
}

it('returns an empty favorite list for a new customer', function () {
    $this->client->jsonRequest('GET', '/favorites', [], catalogCustomerHeaders());

    $response = $this->client->getResponse();
    $payload = json_decode((string) $response->getContent(), true, flags: JSON_THROW_ON_ERROR);

    expect($response->getStatusCode())->toBe(200)
        ->and($payload)->toBe(['productIds' => []]);
});

it('adds removes and clears account favorites', function () {
    $first = favoriteProduct();
    $second = favoriteProduct('Nuvora Mug');
    $headers = catalogCustomerHeaders();

    $this->client->jsonRequest('POST', '/favorites', ['productId' => $first['id']], $headers);
    $this->client->jsonRequest('POST', '/favorites', ['productId' => $second['id']], $headers);
    $added = json_decode((string) $this->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);
    expect($this->client->getResponse()->getStatusCode())->toBe(200)
        ->and($added['productIds'])->toBe([$second['id'], $first['id']]);

    $this->client->jsonRequest('DELETE', '/favorites/'.$first['id'], [], $headers);
    $removed = json_decode((string) $this->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);
    expect($removed['productIds'])->toBe([$second['id']]);

    $this->client->jsonRequest('DELETE', '/favorites', [], $headers);
    $cleared = json_decode((string) $this->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);
    expect($cleared['productIds'])->toBe([]);
});

it('merges guest favorites into the signed-in list', function () {
    $account = favoriteProduct('Account Hoodie');
    $guest = favoriteProduct('Guest Cap');
    $headers = catalogCustomerHeaders();

    $this->client->jsonRequest('POST', '/favorites', ['productId' => $account['id']], $headers);

    $this->client->jsonRequest('POST', '/favorites/merge', [
        'productIds' => [$guest['id'], $account['id']],
    ], $headers);

    $merged = json_decode((string) $this->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);
    expect($this->client->getResponse()->getStatusCode())->toBe(200)
        ->and($merged['productIds'])->toBe([$guest['id'], $account['id']]);
});

it('rejects an unknown product and keeps lists private', function () {
    $this->client->jsonRequest('POST', '/favorites', [
        'productId' => '00000000-0000-4000-8000-000000000000',
    ], catalogCustomerHeaders());
    expect($this->client->getResponse()->getStatusCode())->toBe(404);

    $this->client->jsonRequest('GET', '/favorites');
    expect($this->client->getResponse()->getStatusCode())->toBe(401);

    $product = favoriteProduct();
    $this->client->jsonRequest('POST', '/favorites', ['productId' => $product['id']], catalogCustomerHeaders());

    $this->client->jsonRequest('GET', '/favorites', [], catalogAuthHeaders('other@nuvora.test', Role::customer()));
    $other = json_decode((string) $this->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);
    expect($other['productIds'])->toBe([]);
});
