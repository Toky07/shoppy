<?php

declare(strict_types=1);

namespace App\Address\Application\CommandHandler;

use App\Address\Application\Command\MarkDefaultAddressCommand;
use App\Address\Domain\Exception\SavedAddressNotFound;
use App\Address\Domain\Repository\AddressBookRepository;
use App\Address\Domain\ValueObject\CustomerId;
use App\Address\Domain\ValueObject\SavedAddressId;

final readonly class MarkDefaultAddressCommandHandler
{
    public function __construct(private AddressBookRepository $books)
    {
    }

    public function handle(MarkDefaultAddressCommand $command): void
    {
        $customerId = CustomerId::fromString($command->customerId);
        $book = $this->books->findByCustomerId($customerId);
        $addressId = SavedAddressId::fromString($command->addressId);

        if ($book === null) {
            throw new SavedAddressNotFound($addressId);
        }

        $book->markDefault($addressId);
        $this->books->save($book);
    }
}
