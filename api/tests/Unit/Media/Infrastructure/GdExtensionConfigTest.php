<?php

declare(strict_types=1);

it('installs gd in the api image and ci', function () {
    $root = dirname(__DIR__, 5);
    $dockerfile = (string) file_get_contents($root.'/api/docker/Dockerfile');
    $ci = (string) file_get_contents($root.'/.github/workflows/ci.yml');

    expect($dockerfile)->toMatch('/install-php-extensions[\s\S]*\bgd\b/')
        ->and($ci)->toMatch('/extensions:.*\bgd\b/');
});
