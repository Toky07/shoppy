<?php

declare(strict_types=1);

use App\Product\Infrastructure\Image\CachedProductImageLoader;
use App\Tests\Doubles\FakeHttpDownloader;

it('loads a local image file', function () {
    $directory = sys_get_temp_dir().'/shoppy-images-'.bin2hex(random_bytes(4));
    mkdir($directory, 0777, true);
    $path = $directory.'/tee.png';
    $png = (string) base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==', true);
    file_put_contents($path, $png);

    $image = (new CachedProductImageLoader($directory, new FakeHttpDownloader()))->load($path);

    expect($image->filename)->toBe('tee.png')
        ->and($image->mimeType)->toBe('image/png')
        ->and($image->contents)->toBe($png);
});

it('loads a remote image from cache without downloading again', function () {
    $directory = sys_get_temp_dir().'/shoppy-images-'.bin2hex(random_bytes(4));
    mkdir($directory, 0777, true);
    $png = (string) base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==', true);
    $url = 'https://cdn.shoppy.test/gallery/tee.png';
    file_put_contents($directory.'/'.sha1($url), $png);
    $http = new FakeHttpDownloader();

    $image = (new CachedProductImageLoader($directory, $http))->load($url);

    expect($http->urls)->toBe([])
        ->and($image->filename)->toBe('tee.png')
        ->and($image->mimeType)->toBe('image/png')
        ->and($image->contents)->toBe($png);
});

it('downloads a remote image once and reuses the cache', function () {
    $directory = sys_get_temp_dir().'/shoppy-images-'.bin2hex(random_bytes(4));
    mkdir($directory, 0777, true);
    $png = (string) base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==', true);
    $http = new FakeHttpDownloader(['https://images.unsplash.com/photo-tee?w=800' => $png]);
    $loader = new CachedProductImageLoader($directory, $http);

    $first = $loader->load('https://images.unsplash.com/photo-tee?w=800');
    $second = $loader->load('https://images.unsplash.com/photo-tee?w=800');

    expect($http->urls)->toBe(['https://images.unsplash.com/photo-tee?w=800'])
        ->and($first->contents)->toBe($png)
        ->and($second->contents)->toBe($png)
        ->and($first->filename)->toBe('photo-tee.png')
        ->and($first->mimeType)->toBe('image/png');
});
