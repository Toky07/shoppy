<?php

declare(strict_types=1);

use App\Order\Domain\Exception\InvalidPostalAddress;
use App\Order\Domain\ValueObject\PostalAddress;

it('normalizes a postal address', function () {
    $address = PostalAddress::fromInput(
        '  Ada Lovelace  ',
        '10 rue de la Paix',
        '  ',
        '75002',
        'Paris',
        ' fr ',
        'shippingAddress',
    );

    expect($address->recipient())->toBe('Ada Lovelace')
        ->and($address->line2())->toBeNull()
        ->and($address->country())->toBe('FR')
        ->and($address->toArray())->toBe([
            'recipient' => 'Ada Lovelace',
            'line1' => '10 rue de la Paix',
            'line2' => null,
            'postalCode' => '75002',
            'city' => 'Paris',
            'country' => 'FR',
        ]);
});

it('rejects a country that is not a 2-letter code', function () {
    PostalAddress::fromInput('Ada Lovelace', '10 rue de la Paix', null, '75002', 'Paris', 'France', 'shippingAddress');
})->throws(InvalidPostalAddress::class, 'Country must be a 2-letter code.');
