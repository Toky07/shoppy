<?php

declare(strict_types=1);

use Symfony\Component\Yaml\Yaml;

it('probes get /health instead of the catalog', function () {
    $dockerfile = (string) file_get_contents(repoFile('docker/Dockerfile', 'api/docker/Dockerfile'));

    expect($dockerfile)->toContain('http://127.0.0.1:8080/health')
        ->and($dockerfile)->not->toContain('8080/products');

    $composeFile = repoFileOptional('compose.yaml');

    if ($composeFile === null) {
        return;
    }

    $compose = Yaml::parseFile($composeFile);
    $probe = implode(' ', $compose['services']['api']['healthcheck']['test']);

    expect($probe)->toContain('http://127.0.0.1/health')
        ->and($probe)->not->toContain('/products');
});
