<?php

declare(strict_types=1);

namespace App\Address\Application\CommandHandler;

use App\Address\Application\Command\RemoveSavedAddressCommand;
use App\Address\Domain\Repository\AddressBookRepository;
use App\Address\Domain\ValueObject\CustomerId;
use App\Address\Domain\ValueObject\SavedAddressId;

final readonly class RemoveSavedAddressCommandHandler
{
    public function __construct(private AddressBookRepository $books)
    {
    }

    public function handle(RemoveSavedAddressCommand $command): void
    {
        $customerId = CustomerId::fromString($command->customerId);
        $book = $this->books->findByCustomerId($customerId);

        if ($book === null) {
            return;
        }

        $book->remove(SavedAddressId::fromString($command->addressId));
        $this->books->save($book);
    }
}
