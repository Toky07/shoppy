<?php

declare(strict_types=1);

use Symfony\Component\HttpFoundation\File\UploadedFile;

function invoiceLogoPngFile(string $name = 'logo.png'): UploadedFile
{
    $path = sys_get_temp_dir().'/'.$name;
    file_put_contents(
        $path,
        (string) base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==', true),
    );

    return new UploadedFile($path, $name, 'image/png', null, true);
}

it('starts without an invoice logo', function () {
    $this->client->jsonRequest('GET', '/invoices/branding', [], catalogAdminHeaders());

    $response = $this->client->getResponse();
    $payload = json_decode((string) $response->getContent(), true, flags: JSON_THROW_ON_ERROR);

    expect($response->getStatusCode())->toBe(200)
        ->and($payload)->toBe(['logoUrl' => null, 'mimeType' => null]);
});

it('lets an admin add an invoice logo', function () {
    $this->client->request(
        'POST',
        '/invoices/branding/logo',
        [],
        ['file' => invoiceLogoPngFile()],
        catalogAdminHeaders() + ['HTTP_ACCEPT' => 'application/json'],
    );

    $response = $this->client->getResponse();
    $payload = json_decode((string) $response->getContent(), true, flags: JSON_THROW_ON_ERROR);

    expect($response->getStatusCode())->toBe(200)
        ->and($payload['mimeType'])->toBe('image/png')
        ->and($payload['logoUrl'])->toStartWith('/uploads/invoices/logos/')
        ->and($payload['logoUrl'])->toEndWith('.png');

    $this->client->jsonRequest('GET', '/invoices/branding', [], catalogAdminHeaders());
    $branding = json_decode((string) $this->client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);

    expect($this->client->getResponse()->getStatusCode())->toBe(200)
        ->and($branding)->toBe($payload);
});

it('rejects a customer changing the invoice logo', function () {
    $this->client->request(
        'POST',
        '/invoices/branding/logo',
        [],
        ['file' => invoiceLogoPngFile('customer-logo.png')],
        catalogCustomerHeaders() + ['HTTP_ACCEPT' => 'application/json'],
    );

    $response = $this->client->getResponse();
    $payload = json_decode((string) $response->getContent(), true, flags: JSON_THROW_ON_ERROR);

    expect($response->getStatusCode())->toBe(403)
        ->and($payload['error']['code'])->toBe('forbidden');
});

it('rejects unauthenticated access to invoice branding', function () {
    $this->client->jsonRequest('GET', '/invoices/branding');

    $response = $this->client->getResponse();
    $payload = json_decode((string) $response->getContent(), true, flags: JSON_THROW_ON_ERROR);

    expect($response->getStatusCode())->toBe(401)
        ->and($payload['error']['code'])->toBe('unauthenticated');
});
