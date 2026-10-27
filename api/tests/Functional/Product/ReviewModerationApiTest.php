<?php

declare(strict_types=1);

use App\User\Domain\ValueObject\Role;

function reviewModerationProduct(): array
{
    test()->client->jsonRequest('POST', '/products', [
        'name' => 'Review Tee',
        'priceCents' => 1999,
        'stock' => 10,
    ], catalogAdminHeaders());

    return json_decode((string) test()->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);
}

function submitReview(array $headers, string $productId, string $body = 'Très beau tee.'): array
{
    test()->client->jsonRequest('POST', '/products/'.$productId.'/reviews', [
        'rating' => 5,
        'body' => $body,
    ], $headers);

    return json_decode((string) test()->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);
}

it('lets a customer report a review and an admin hide it from the storefront', function () {
    $product = reviewModerationProduct();
    $author = catalogCustomerHeaders();
    $reporter = catalogAuthHeaders('reporter@nuvora.test', Role::customer());
    $review = submitReview($author, $product['id'], 'Avis à signaler.');

    $this->client->jsonRequest(
        'POST',
        '/products/'.$product['id'].'/reviews/'.$review['id'].'/report',
        [],
        $reporter,
    );
    expect($this->client->getResponse()->getStatusCode())->toBe(204);

    $this->client->jsonRequest('GET', '/admin/reviews', [], catalogAdminHeaders());
    $adminList = json_decode((string) $this->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);
    expect($this->client->getResponse()->getStatusCode())->toBe(200)
        ->and($adminList['total'])->toBeGreaterThanOrEqual(1)
        ->and($adminList['items'][0])->toMatchArray([
            'id' => $review['id'],
            'productId' => $product['id'],
            'productName' => 'Review Tee',
            'reported' => true,
            'hidden' => false,
            'body' => 'Avis à signaler.',
        ]);

    $this->client->jsonRequest('POST', '/admin/reviews/'.$review['id'].'/hide', [], catalogAdminHeaders());
    expect($this->client->getResponse()->getStatusCode())->toBe(204);

    $this->client->jsonRequest('GET', '/products/'.$product['id'].'/reviews');
    $public = json_decode((string) $this->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);
    expect($this->client->getResponse()->getStatusCode())->toBe(200)
        ->and($public['count'])->toBe(0)
        ->and($public['items'])->toBe([]);

    $this->client->jsonRequest('POST', '/admin/reviews/'.$review['id'].'/restore', [], catalogAdminHeaders());
    expect($this->client->getResponse()->getStatusCode())->toBe(204);

    $this->client->jsonRequest('GET', '/products/'.$product['id'].'/reviews');
    $restored = json_decode((string) $this->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);
    expect($restored['count'])->toBe(1)
        ->and($restored['items'][0]['body'])->toBe('Avis à signaler.');
});

it('refuses reporting your own review', function () {
    $product = reviewModerationProduct();
    $author = catalogCustomerHeaders();
    $review = submitReview($author, $product['id']);

    $this->client->jsonRequest(
        'POST',
        '/products/'.$product['id'].'/reviews/'.$review['id'].'/report',
        [],
        $author,
    );
    $payload = json_decode((string) $this->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);

    expect($this->client->getResponse()->getStatusCode())->toBe(403)
        ->and($payload['error']['code'])->toBe('cannot_report_own_review');
});

it('rejects an unauthenticated report and a customer hide', function () {
    $product = reviewModerationProduct();
    $review = submitReview(catalogCustomerHeaders(), $product['id']);

    $this->client->jsonRequest('POST', '/products/'.$product['id'].'/reviews/'.$review['id'].'/report');
    expect($this->client->getResponse()->getStatusCode())->toBe(401);

    $this->client->jsonRequest('POST', '/admin/reviews/'.$review['id'].'/hide', [], catalogCustomerHeaders());
    $payload = json_decode((string) $this->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);
    expect($this->client->getResponse()->getStatusCode())->toBe(403)
        ->and($payload['error']['code'])->toBe('forbidden');
});
