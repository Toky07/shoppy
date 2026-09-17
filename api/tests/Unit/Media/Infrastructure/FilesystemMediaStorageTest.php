<?php

declare(strict_types=1);

use App\Media\Infrastructure\Storage\FilesystemMediaStorage;

it('stores a file under a wordpress year/month directory', function () {
    $root = sys_get_temp_dir().'/shoppy-media-'.bin2hex(random_bytes(4));
    $storage = new FilesystemMediaStorage($root);
    $now = new DateTimeImmutable('2026-09-16T12:00:00+00:00');

    $stored = $storage->store('Nuvora Tee.PNG', 'png', 'binary-bytes', $now);

    expect($stored->relativePath)->toBe('2026/09/nuvora-tee.png')
        ->and($stored->filename)->toBe('nuvora-tee.png')
        ->and($root.'/2026/09/nuvora-tee.png')->toBeFile()
        ->and((string) file_get_contents($root.'/2026/09/nuvora-tee.png'))->toBe('binary-bytes');
});

it('makes colliding filenames unique', function () {
    $root = sys_get_temp_dir().'/shoppy-media-'.bin2hex(random_bytes(4));
    $storage = new FilesystemMediaStorage($root);
    $now = new DateTimeImmutable('2026-09-16T12:00:00+00:00');

    $storage->store('tee.png', 'png', 'one', $now);
    $second = $storage->store('tee.png', 'png', 'two', $now);

    expect($second->filename)->toBe('tee-1.png')
        ->and((string) file_get_contents($root.'/2026/09/tee-1.png'))->toBe('two');
});

it('deletes a stored file', function () {
    $root = sys_get_temp_dir().'/shoppy-media-'.bin2hex(random_bytes(4));
    $storage = new FilesystemMediaStorage($root);
    $now = new DateTimeImmutable('2026-09-16T12:00:00+00:00');
    $stored = $storage->store('tee.png', 'png', 'one', $now);

    $storage->delete($stored->relativePath);

    expect($root.'/2026/09/tee.png')->not->toBeFile();
});
