<?php

declare(strict_types=1);

use App\Address\Application\Command\MarkDefaultAddressCommand;
use App\Address\Application\Command\RemoveSavedAddressCommand;
use App\Address\Application\CommandHandler\MarkDefaultAddressCommandHandler;
use App\Address\Application\CommandHandler\RemoveSavedAddressCommandHandler;
use App\Address\Application\Query\GetAddressBookQuery;
use App\Address\Application\QueryHandler\GetAddressBookQueryHandler;
use App\Address\Domain\Entity\AddressBook;
use App\Address\Domain\Exception\SavedAddressNotFound;
use App\Address\Domain\ValueObject\CustomerId;
use App\Address\Infrastructure\Persistence\InMemoryAddressBookRepository;
use App\Order\Domain\ValueObject\PostalAddress;

function seededBook(): array
{
    $customerId = '11111111-1111-4111-8111-111111111111';
    $books = new InMemoryAddressBookRepository();
    $book = AddressBook::empty(CustomerId::fromString($customerId));
    $book->remember(samplePostalAddress());
    $book->remember(PostalAddress::fromInput(
        'Ada Lovelace',
        '2 place Bellecour',
        null,
        '69002',
        'Lyon',
        'FR',
        'address',
    ));
    $books->save($book);

    return [$customerId, $books, $book];
}

it('returns an empty book when the customer has no addresses yet', function () {
    $response = (new GetAddressBookQueryHandler(new InMemoryAddressBookRepository()))
        ->handle(new GetAddressBookQuery('11111111-1111-4111-8111-111111111111'));

    expect($response->toArray())->toBe(['addresses' => []]);
});

it('removes a saved address', function () {
    [$customerId, $books, $book] = seededBook();
    $lyonId = $book->default()?->id()->value();

    (new RemoveSavedAddressCommandHandler($books))->handle(new RemoveSavedAddressCommand($customerId, $lyonId));

    $response = (new GetAddressBookQueryHandler($books))->handle(new GetAddressBookQuery($customerId));

    expect($response->addresses)->toHaveCount(1)
        ->and($response->addresses[0]['city'])->toBe('Paris')
        ->and($response->addresses[0]['isDefault'])->toBeTrue();
});

it('marks another address as default', function () {
    [$customerId, $books, $book] = seededBook();
    $parisId = $book->addresses()[1]->id()->value();

    (new MarkDefaultAddressCommandHandler($books))->handle(new MarkDefaultAddressCommand($customerId, $parisId));

    $response = (new GetAddressBookQueryHandler($books))->handle(new GetAddressBookQuery($customerId));

    expect($response->addresses[0]['isDefault'])->toBeFalse()
        ->and($response->addresses[1]['isDefault'])->toBeTrue()
        ->and($response->addresses[1]['id'])->toBe($parisId);
});

it('rejects a default on an unknown address', function () {
    [$customerId, $books] = seededBook();

    (new MarkDefaultAddressCommandHandler($books))->handle(new MarkDefaultAddressCommand(
        $customerId,
        '00000000-0000-4000-8000-000000000000',
    ));
})->throws(SavedAddressNotFound::class);
