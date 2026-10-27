<?php

declare(strict_types=1);

use App\Address\Domain\Entity\AddressBook;
use App\Address\Domain\Repository\AddressBookRepository;
use App\Address\Domain\ValueObject\CustomerId;
use App\Order\Domain\ValueObject\PostalAddress;
use Doctrine\ORM\EntityManagerInterface;

it('persists an address book by customer', function () {
    $customerId = CustomerId::fromString('11111111-1111-4111-8111-111111111111');
    $book = AddressBook::empty($customerId);
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

    $repository = self::getContainer()->get(AddressBookRepository::class);
    $entityManager = self::getContainer()->get(EntityManagerInterface::class);

    $repository->save($book);
    $entityManager->clear();

    $found = $repository->findByCustomerId($customerId);

    expect($found)->not->toBeNull()
        ->and($found->addresses())->toHaveCount(2)
        ->and($found->addresses()[0]->postal()->city())->toBe('Lyon')
        ->and($found->addresses()[0]->isDefault())->toBeTrue()
        ->and($found->addresses()[1]->postal()->city())->toBe('Paris')
        ->and($found->addresses()[1]->isDefault())->toBeFalse();

    $found->remove($found->addresses()[0]->id());
    $repository->save($found);
    $entityManager->clear();

    $updated = $repository->findByCustomerId($customerId);

    expect($updated)->not->toBeNull()
        ->and($updated->addresses())->toHaveCount(1)
        ->and($updated->default()?->postal()->city())->toBe('Paris')
        ->and($updated->default()?->isDefault())->toBeTrue();
});
