<?php

declare(strict_types=1);

namespace App\Address\Infrastructure\Persistence;

use App\Address\Domain\Entity\AddressBook;
use App\Address\Domain\Repository\AddressBookRepository;
use App\Address\Domain\ValueObject\CustomerId;

final class InMemoryAddressBookRepository implements AddressBookRepository
{
    /** @var array<string, AddressBook> */
    private array $byCustomerId = [];

    public int $saveCalls = 0;

    public function save(AddressBook $book): void
    {
        $this->saveCalls++;
        $this->byCustomerId[$book->customerId()->value()] = $book;
    }

    public function findByCustomerId(CustomerId $customerId): ?AddressBook
    {
        $book = $this->byCustomerId[$customerId->value()] ?? null;

        return $book === null ? null : clone $book;
    }
}
