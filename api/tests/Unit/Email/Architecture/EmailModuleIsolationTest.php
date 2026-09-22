<?php

declare(strict_types=1);

it('does not depend on another bounded context', function () {
    $root = dirname(__DIR__, 4).'/src/Email';

    expect(is_dir($root))->toBeTrue();

    $forbidden = [
        'App\\Order\\',
        'App\\User\\',
        'App\\Auth\\',
        'App\\Cart\\',
        'App\\Payment\\',
        'App\\Product\\',
        'App\\Media\\',
    ];

    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root));

    foreach ($iterator as $file) {
        if (!$file->isFile() || $file->getExtension() !== 'php') {
            continue;
        }

        $contents = (string) file_get_contents($file->getPathname());

        foreach ($forbidden as $namespace) {
            expect($contents)->not->toContain($namespace);
        }
    }
});

it('keeps Symfony out of the email domain and application layers', function () {
    $root = dirname(__DIR__, 4).'/src/Email';

    foreach (['Domain', 'Application'] as $layer) {
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root.'/'.$layer));

        foreach ($iterator as $file) {
            if (!$file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }

            expect((string) file_get_contents($file->getPathname()))->not->toContain('Symfony\\');
        }
    }
});
