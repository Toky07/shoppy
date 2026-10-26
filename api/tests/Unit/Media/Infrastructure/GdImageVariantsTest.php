<?php

declare(strict_types=1);

use App\Media\Application\MediaDerivative;
use App\Media\Infrastructure\Image\GdImageVariants;
use App\Tests\Doubles\FakeMediaStorage;

function rasterPng(int $width, int $height): string
{
    $image = imagecreatetruecolor($width, $height);
    imagefilledrectangle($image, 0, 0, $width, $height, imagecolorallocate($image, 20, 80, 160));
    ob_start();
    imagepng($image);
    imagedestroy($image);

    return (string) ob_get_clean();
}

function imageWidth(string $binary): int
{
    $image = imagecreatefromstring($binary);
    $width = imagesx($image);
    imagedestroy($image);

    return $width;
}

it('writes thumb, card and detail files without upscaling', function () {
    $storage = new FakeMediaStorage();
    $variants = new GdImageVariants($storage);
    $png = rasterPng(400, 200);

    $variants->generate('2026/09/photo.png', $png, 'image/png');

    expect($storage->files)->toHaveKeys([
        '2026/09/photo.thumb.png',
        '2026/09/photo.card.png',
        '2026/09/photo.detail.png',
    ])
        ->and(imageWidth($storage->files['2026/09/photo.thumb.png']))->toBe(128)
        ->and(imageWidth($storage->files['2026/09/photo.card.png']))->toBe(400)
        ->and(imageWidth($storage->files['2026/09/photo.detail.png']))->toBe(400);
})->skip(!extension_loaded('gd'), 'The GD extension is required to generate image derivatives.');

it('skips svg and gif uploads', function () {
    $storage = new FakeMediaStorage();
    $variants = new GdImageVariants($storage);

    $variants->generate('2026/09/logo.svg', '<svg></svg>', 'image/svg+xml');
    $variants->generate('2026/09/loop.gif', 'GIF89a', 'image/gif');

    expect($storage->files)->toBe([]);
});

it('deletes every derivative of an original', function () {
    $storage = new FakeMediaStorage();
    $variants = new GdImageVariants($storage);
    $png = rasterPng(64, 64);

    $variants->generate('2026/09/photo.png', $png, 'image/png');
    $variants->delete('2026/09/photo.png');

    foreach (MediaDerivative::cases() as $derivative) {
        expect($storage->files)->not->toHaveKey($derivative->pathFor('2026/09/photo.png'));
    }
})->skip(!extension_loaded('gd'), 'The GD extension is required to generate image derivatives.');
