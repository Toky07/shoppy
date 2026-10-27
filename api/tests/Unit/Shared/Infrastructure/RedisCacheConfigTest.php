<?php

declare(strict_types=1);

use Symfony\Component\Yaml\Yaml;

it('configures redis as the application cache', function () {
    $config = Yaml::parseFile(repoFile('config/packages/cache.yaml', 'api/config/packages/cache.yaml'));

    expect($config['framework']['cache']['app'])->toBe('cache.adapter.redis')
        ->and($config['framework']['cache']['default_redis_provider'])->toBe('%env(REDIS_URL)%')
        ->and($config['when@test']['framework']['cache']['app'])->toBe('cache.adapter.array');
});

it('deploys redis next to the api', function () {
    $devFile = repoFileOptional('compose.yaml');
    $prodFile = repoFileOptional('compose.prod.yaml');

    if ($devFile === null || $prodFile === null) {
        expect(repoFile('config/packages/cache.yaml', 'api/config/packages/cache.yaml'))->toBeString();

        return;
    }

    $dev = Yaml::parseFile($devFile);
    $prod = Yaml::parseFile($prodFile);

    expect($dev['services'])->toHaveKey('redis')
        ->and($dev['services']['api']['environment']['REDIS_URL'] ?? null)->toBe('redis://redis:6379')
        ->and($dev['services']['api']['depends_on'])->toHaveKey('redis')
        ->and($prod['services'])->toHaveKey('redis')
        ->and($prod['services']['api']['environment']['REDIS_URL'] ?? null)->toBe('redis://redis:6379')
        ->and($prod['services']['api']['depends_on'])->toHaveKey('redis');
});
