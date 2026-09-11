<?php

declare(strict_types=1);

use App\Auth\Infrastructure\Security\RandomTokenGenerator;

it('generates a unique opaque token and its hash', function () {
    $generator = new RandomTokenGenerator();

    $first = $generator->generate();
    $second = $generator->generate();

    expect($first->plain)->toMatch('/^[0-9a-f]{64}$/')
        ->and($first->hash->value())->toBe(hash('sha256', $first->plain))
        ->and($first->plain)->not->toBe($second->plain)
        ->and($first->hash->value())->not->toBe($first->plain);
});
