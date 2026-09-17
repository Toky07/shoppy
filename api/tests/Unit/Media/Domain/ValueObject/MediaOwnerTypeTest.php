<?php

declare(strict_types=1);

use App\Media\Domain\Exception\InvalidMediaOwnerType;
use App\Media\Domain\ValueObject\MediaOwnerType;

it('accepts the product alias and stores the entity class name', function () {
    $type = MediaOwnerType::fromInput('product');

    expect($type->className())->toBe('App\\Product\\Domain\\Entity\\Product')
        ->and($type->alias())->toBe('product');
});

it('accepts the product entity class name', function () {
    $type = MediaOwnerType::fromInput('App\\Product\\Domain\\Entity\\Product');

    expect($type->className())->toBe('App\\Product\\Domain\\Entity\\Product')
        ->and($type->alias())->toBe('product');
});

it('rejects an unknown owner type', function () {
    MediaOwnerType::fromInput('order');
})->throws(InvalidMediaOwnerType::class);

it('rejects an empty owner type', function () {
    MediaOwnerType::fromInput('  ');
})->throws(InvalidMediaOwnerType::class);
