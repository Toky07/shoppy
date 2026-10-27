<?php

declare(strict_types=1);

namespace App\Address\Domain\Repository;

use App\Address\Domain\Entity\AddressBook;
use App\Address\Domain\ValueObject\CustomerId;

interface AddressBookRepository
{
    public function save(AddressBook $book): void;

    public function findByCustomerId(CustomerId $customerId): ?AddressBook;
}
