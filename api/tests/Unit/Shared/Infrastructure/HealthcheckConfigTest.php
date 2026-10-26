<?php

declare(strict_types=1);

use Symfony\Component\Yaml\Yaml;

function shoppyRepoRoot(): string
{
    return dirname(__DIR__, 5);
}

it('probes get /health instead of the catalog', function () {
    $compose = Yaml::parseFile(shoppyRepoRoot().'/compose.yaml');
    $probe = implode(' ', $compose['services']['api']['healthcheck']['test']);
    $dockerfile = (string) file_get_contents(shoppyRepoRoot().'/api/docker/Dockerfile');

    expect($probe)->toContain('http://127.0.0.1/health')
        ->and($probe)->not->toContain('/products')
        ->and($dockerfile)->toContain('http://127.0.0.1:8080/health')
        ->and($dockerfile)->not->toContain('8080/products');
});
