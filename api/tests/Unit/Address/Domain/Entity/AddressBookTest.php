<?php

declare(strict_types=1);

use App\Address\Domain\Entity\AddressBook;
use App\Address\Domain\Exception\AddressBookFull;
use App\Address\Domain\Exception\SavedAddressNotFound;
use App\Address\Domain\ValueObject\CustomerId;
use App\Address\Domain\ValueObject\SavedAddressId;
use App\Order\Domain\ValueObject\PostalAddress;

function emptyBook(): AddressBook
{
    return AddressBook::empty(CustomerId::fromString('11111111-1111-4111-8111-111111111111'));
}

function lyonAddress(): PostalAddress
{
    return PostalAddress::fromInput(
        'Ada Lovelace',
        '2 place Bellecour',
        null,
        '69002',
        'Lyon',
        'FR',
        'address',
    );
}

function numberedAddress(int $n): PostalAddress
{
    return PostalAddress::fromInput(
        'Ada Lovelace',
        $n.' rue de la Paix',
        null,
        '75002',
        'Paris',
        'FR',
        'address',
    );
}

it('starts empty', function () {
    $book = emptyBook();

    expect($book->addresses())->toBe([])
        ->and($book->default())->toBeNull();
});

it('remembers a shipping address as the default', function () {
    $book = emptyBook();
    $book->remember(samplePostalAddress());

    expect($book->addresses())->toHaveCount(1)
        ->and($book->default()?->isDefault())->toBeTrue()
        ->and($book->default()?->postal()->toArray())->toBe(samplePostalAddress()->toArray());
});

it('does not duplicate the same address and keeps it default', function () {
    $book = emptyBook();
    $book->remember(samplePostalAddress());
    $book->remember(lyonAddress());
    $book->remember(samplePostalAddress());

    expect($book->addresses())->toHaveCount(2)
        ->and($book->default()?->postal()->city())->toBe('Paris')
        ->and($book->addresses()[0]->postal()->city())->toBe('Lyon')
        ->and($book->addresses()[1]->isDefault())->toBeTrue();
});

it('prepends a new address and makes it the default', function () {
    $book = emptyBook();
    $book->remember(samplePostalAddress());
    $book->remember(lyonAddress());

    expect($book->addresses())->toHaveCount(2)
        ->and($book->addresses()[0]->postal()->city())->toBe('Lyon')
        ->and($book->addresses()[0]->isDefault())->toBeTrue()
        ->and($book->addresses()[1]->isDefault())->toBeFalse();
});

it('can remember a billing address without stealing the default', function () {
    $book = emptyBook();
    $book->remember(samplePostalAddress());
    $book->remember(lyonAddress(), asDefault: false);

    expect($book->addresses())->toHaveCount(2)
        ->and($book->default()?->postal()->city())->toBe('Paris')
        ->and($book->addresses()[0]->postal()->city())->toBe('Lyon')
        ->and($book->addresses()[0]->isDefault())->toBeFalse();
});

it('removes an address and promotes the next default', function () {
    $book = emptyBook();
    $book->remember(samplePostalAddress());
    $book->remember(lyonAddress());
    $defaultId = $book->default()?->id();

    expect($defaultId)->not->toBeNull();
    $book->remove($defaultId);

    expect($book->addresses())->toHaveCount(1)
        ->and($book->default()?->postal()->city())->toBe('Paris')
        ->and($book->default()?->isDefault())->toBeTrue();
});

it('marks another address as default', function () {
    $book = emptyBook();
    $book->remember(samplePostalAddress());
    $book->remember(lyonAddress());
    $parisId = $book->addresses()[1]->id();

    $book->markDefault($parisId);

    expect($book->default()?->id()->value())->toBe($parisId->value())
        ->and($book->addresses()[0]->isDefault())->toBeFalse();
});

it('rejects marking an unknown address as default', function () {
    emptyBook()->markDefault(SavedAddressId::fromString('00000000-0000-4000-8000-000000000000'));
})->throws(SavedAddressNotFound::class);

it('refuses an eleventh distinct address', function () {
    $book = emptyBook();

    for ($n = 1; $n <= AddressBook::MAX_ADDRESSES; $n++) {
        $book->remember(numberedAddress($n));
    }

    $book->remember(numberedAddress(11));
})->throws(AddressBookFull::class);
