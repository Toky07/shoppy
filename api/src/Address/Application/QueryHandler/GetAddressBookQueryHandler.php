<?php

declare(strict_types=1);

namespace App\Address\Application\QueryHandler;

use App\Address\Application\Query\GetAddressBookQuery;
use App\Address\Application\Response\AddressBookResponse;
use App\Address\Domain\Repository\AddressBookRepository;
use App\Address\Domain\ValueObject\CustomerId;

final readonly class GetAddressBookQueryHandler
{
    public function __construct(private AddressBookRepository $books)
    {
    }

    public function handle(GetAddressBookQuery $query): AddressBookResponse
    {
        return AddressBookResponse::fromBook(
            $this->books->findByCustomerId(CustomerId::fromString($query->customerId)),
        );
    }
}
