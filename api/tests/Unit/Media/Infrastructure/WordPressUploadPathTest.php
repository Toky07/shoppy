<?php

declare(strict_types=1);

use App\Media\Infrastructure\Storage\WordPressUploadPath;

it('builds a year/month directory like wordpress', function () {
    $now = new DateTimeImmutable('2026-09-16T12:00:00+00:00');

    expect(WordPressUploadPath::relativeDirectory($now))->toBe('2026/09');
});

it('sanitizes the original filename and keeps a known extension', function () {
    expect(WordPressUploadPath::sanitizeFilename('Nuvora Tee!.PNG', 'png'))->toBe('nuvora-tee.png');
});

it('uses the mime extension when the original name has none', function () {
    expect(WordPressUploadPath::sanitizeFilename('photo', 'jpg'))->toBe('photo.jpg');
});

it('falls back to file when the name is empty after sanitizing', function () {
    expect(WordPressUploadPath::sanitizeFilename('!!!', 'svg'))->toBe('file.svg');
});

it('appends a numeric suffix when the filename already exists', function () {
    $directory = sys_get_temp_dir().'/shoppy-uploads-'.bin2hex(random_bytes(4));
    mkdir($directory, 0777, true);
    file_put_contents($directory.'/tee.jpg', 'a');

    $unique = WordPressUploadPath::uniqueFilename($directory, 'tee.jpg');

    expect($unique)->toBe('tee-1.jpg');
});
