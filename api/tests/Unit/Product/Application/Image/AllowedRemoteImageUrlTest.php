<?php

declare(strict_types=1);

use App\Product\Application\Image\AllowedRemoteImageUrl;
use App\Product\Domain\Exception\RemoteImageUrlNotAllowed;
use App\Tests\Doubles\FakeHostAddresses;

function allowedRemoteImageUrl(?FakeHostAddresses $hosts = null): AllowedRemoteImageUrl
{
    return new AllowedRemoteImageUrl($hosts ?? new FakeHostAddresses([
        'images.unsplash.com' => ['151.101.1.181'],
        'cdn.shoppy.test' => ['93.184.216.34'],
    ]));
}

it('allows https urls on public hosts', function (string $url) {
    expect(fn () => allowedRemoteImageUrl()->assertSafe($url))
        ->not->toThrow(RemoteImageUrlNotAllowed::class);
})->with([
    'https://images.unsplash.com/photo-tee?w=800',
    'https://cdn.shoppy.test/mug.png',
    'https://images.unsplash.com:443/photo-tee',
]);

it('rejects urls that are not public https', function (string $url) {
    allowedRemoteImageUrl()->assertSafe($url);
})->with([
    'http://images.unsplash.com/photo-tee',
    'file:///etc/passwd',
    'ftp://cdn.shoppy.test/mug.png',
    'gopher://cdn.shoppy.test/mug.png',
    'https://user:pass@images.unsplash.com/photo-tee',
    'https://127.0.0.1/latest/meta-data/',
    'https://10.0.0.8/image.png',
    'https://192.168.1.10/image.png',
    'https://172.16.0.4/image.png',
    'https://169.254.169.254/latest/meta-data/',
    'https://[::1]/image.png',
    'https://0.0.0.0/image.png',
    'https://localhost/image.png',
    'https://images.unsplash.com:8080/photo-tee',
    'https://internal.shoppy.test/image.png',
])->throws(RemoteImageUrlNotAllowed::class);

it('rejects a hostname that resolves to a private address', function () {
    $guard = allowedRemoteImageUrl(new FakeHostAddresses([
        'cdn.shoppy.test' => ['127.0.0.1'],
    ]));

    $guard->assertSafe('https://cdn.shoppy.test/mug.png');
})->throws(RemoteImageUrlNotAllowed::class);
