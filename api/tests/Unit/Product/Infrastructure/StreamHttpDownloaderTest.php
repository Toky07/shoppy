<?php

declare(strict_types=1);

use App\Product\Application\Image\AllowedRemoteImageUrl;
use App\Product\Domain\Exception\RemoteImageUrlNotAllowed;
use App\Product\Infrastructure\Image\StreamHttpDownloader;
use App\Tests\Doubles\FakeHostAddresses;

it('does not fetch a private or non-https image url', function (string $url) {
    $downloader = new StreamHttpDownloader(new AllowedRemoteImageUrl(new FakeHostAddresses([
        'localhost' => ['127.0.0.1'],
    ])));

    $downloader->get($url);
})->with([
    'http://127.0.0.1/secret',
    'https://127.0.0.1/secret',
    'https://localhost/secret',
    'file:///etc/passwd',
])->throws(RemoteImageUrlNotAllowed::class);
