<?php

declare(strict_types=1);

namespace App\Address\Infrastructure\Persistence\Doctrine;

use App\Address\Domain\Entity\AddressBook;
use App\Address\Domain\Repository\AddressBookRepository;
use App\Address\Domain\ValueObject\CustomerId;
use App\Address\Infrastructure\Persistence\Doctrine\Entity\AddressBookRecord;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrineAddressBookRepository implements AddressBookRepository
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function save(AddressBook $book): void
    {
        $record = $this->entityManager->find(AddressBookRecord::class, $book->customerId()->value());

        if ($record === null) {
            $this->entityManager->persist(AddressBookRecord::fromDomain($book));
        } else {
            $record->updateFromDomain($book);
        }

        $this->entityManager->flush();
    }

    public function findByCustomerId(CustomerId $customerId): ?AddressBook
    {
        $record = $this->entityManager->find(AddressBookRecord::class, $customerId->value());

        return $record?->toDomain();
    }
}
