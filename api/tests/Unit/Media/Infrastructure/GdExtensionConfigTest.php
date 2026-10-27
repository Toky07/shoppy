<?php

declare(strict_types=1);

it('installs gd in the api image and ci', function () {
    $dockerfile = (string) file_get_contents(repoFile('docker/Dockerfile', 'api/docker/Dockerfile'));

    expect($dockerfile)->toMatch('/install-php-extensions[\s\S]*\bgd\b/');

    $ciFile = repoFileOptional('.github/workflows/ci.yml');

    if ($ciFile === null) {
        return;
    }

    expect((string) file_get_contents($ciFile))->toMatch('/extensions:.*\bgd\b/');
});
