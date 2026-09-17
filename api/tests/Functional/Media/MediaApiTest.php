<?php

declare(strict_types=1);

use App\Shared\Domain\Clock;
use Symfony\Component\HttpFoundation\File\UploadedFile;

function mediaPngFile(string $name = 'tee.png'): UploadedFile
{
    $path = sys_get_temp_dir().'/'.$name;
    file_put_contents(
        $path,
        (string) base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==', true),
    );

    return new UploadedFile($path, $name, 'image/png', null, true);
}

function createCatalogProduct(): string
{
    test()->client->jsonRequest('POST', '/products', [
        'name' => 'Nuvora Tee',
        'priceCents' => 1999,
    ], catalogAdminHeaders());

    $payload = json_decode((string) test()->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);

    return $payload['id'];
}

it('uploads media for a product and lists it', function () {
    $productId = createCatalogProduct();

    $this->client->request(
        'POST',
        '/media',
        [
            'ownerType' => 'product',
            'ownerId' => $productId,
        ],
        [
            'file' => mediaPngFile('Nuvora Tee.PNG'),
        ],
        catalogAdminHeaders() + ['HTTP_ACCEPT' => 'application/json'],
    );

    $response = $this->client->getResponse();
    $payload = json_decode((string) $response->getContent(), true, flags: JSON_THROW_ON_ERROR);
    $now = self::getContainer()->get(Clock::class)->now();
    $prefix = '/uploads/'.$now->format('Y/m').'/';

    expect($response->getStatusCode())->toBe(201)
        ->and($payload['ownerType'])->toBe('product')
        ->and($payload['ownerId'])->toBe($productId)
        ->and($payload['filename'])->toBe('nuvora-tee.png')
        ->and($payload['url'])->toStartWith($prefix)
        ->and($payload['url'])->toEndWith('nuvora-tee.png')
        ->and($payload['mimeType'])->toBe('image/png')
        ->and($payload['position'])->toBe(0)
        ->and($response->headers->get('Location'))->toBe('/media/'.$payload['id']);

    $this->client->jsonRequest('GET', '/media?ownerType=product&ownerId='.$productId);
    $list = json_decode((string) $this->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);

    expect($this->client->getResponse()->getStatusCode())->toBe(200)
        ->and($list['items'])->toHaveCount(1)
        ->and($list['items'][0]['id'])->toBe($payload['id']);

    $this->client->jsonRequest('GET', '/products/'.$productId);
    $product = json_decode((string) $this->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);

    expect($product['imageUrl'])->toBe($payload['url'])
        ->and($product['imageUrls'])->toBe([$payload['url']]);
});

it('accepts the product class name as owner type', function () {
    $productId = createCatalogProduct();

    $this->client->request(
        'POST',
        '/media',
        [
            'ownerType' => 'App\\Product\\Domain\\Entity\\Product',
            'ownerId' => $productId,
        ],
        [
            'file' => mediaPngFile('class-owner.png'),
        ],
        catalogAdminHeaders() + ['HTTP_ACCEPT' => 'application/json'],
    );

    $payload = json_decode((string) $this->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);

    expect($this->client->getResponse()->getStatusCode())->toBe(201)
        ->and($payload['ownerType'])->toBe('product');
});

it('rejects upload without a file', function () {
    $productId = createCatalogProduct();

    $this->client->request(
        'POST',
        '/media',
        [
            'ownerType' => 'product',
            'ownerId' => $productId,
        ],
        [],
        catalogAdminHeaders() + ['HTTP_ACCEPT' => 'application/json'],
    );

    $payload = json_decode((string) $this->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);

    expect($this->client->getResponse()->getStatusCode())->toBe(400)
        ->and($payload['error']['code'])->toBe('validation_error')
        ->and($payload['error']['violations'])->toBe([
            ['field' => 'file', 'message' => 'This field is required.'],
        ]);
});

it('rejects an unsupported mime type', function () {
    $productId = createCatalogProduct();
    $path = sys_get_temp_dir().'/notes.txt';
    file_put_contents($path, 'hello');

    $this->client->request(
        'POST',
        '/media',
        [
            'ownerType' => 'product',
            'ownerId' => $productId,
        ],
        [
            'file' => new UploadedFile($path, 'notes.txt', 'text/plain', null, true),
        ],
        catalogAdminHeaders() + ['HTTP_ACCEPT' => 'application/json'],
    );

    $payload = json_decode((string) $this->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);

    expect($this->client->getResponse()->getStatusCode())->toBe(400)
        ->and($payload['error']['violations'][0]['field'])->toBe('mimeType');
});

it('forbids customers from uploading media', function () {
    $productId = createCatalogProduct();

    $this->client->request(
        'POST',
        '/media',
        [
            'ownerType' => 'product',
            'ownerId' => $productId,
        ],
        [
            'file' => mediaPngFile('forbidden.png'),
        ],
        catalogCustomerHeaders() + ['HTTP_ACCEPT' => 'application/json'],
    );

    expect($this->client->getResponse()->getStatusCode())->toBe(403);
});

it('deletes media', function () {
    $productId = createCatalogProduct();

    $this->client->request(
        'POST',
        '/media',
        [
            'ownerType' => 'product',
            'ownerId' => $productId,
        ],
        [
            'file' => mediaPngFile('delete-me.png'),
        ],
        catalogAdminHeaders() + ['HTTP_ACCEPT' => 'application/json'],
    );

    $created = json_decode((string) $this->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);

    $this->client->jsonRequest('DELETE', '/media/'.$created['id'], [], catalogAdminHeaders());

    expect($this->client->getResponse()->getStatusCode())->toBe(204);

    $this->client->jsonRequest('GET', '/media?ownerType=product&ownerId='.$productId);
    $list = json_decode((string) $this->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);

    expect($list['items'])->toBe([]);
});

it('returns not found when deleting unknown media', function () {
    $this->client->jsonRequest(
        'DELETE',
        '/media/aa0e8400-e29b-41d4-a716-446655440000',
        [],
        catalogAdminHeaders(),
    );

    $payload = json_decode((string) $this->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);

    expect($this->client->getResponse()->getStatusCode())->toBe(404)
        ->and($payload['error']['code'])->toBe('media_not_found');
});
