<?php

declare(strict_types=1);

namespace App\Address\Infrastructure\Persistence\Doctrine\Entity;

use App\Address\Domain\Entity\AddressBook;
use App\Address\Domain\ValueObject\CustomerId;
use App\Address\Domain\ValueObject\SavedAddress;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'address_books')]
class AddressBookRecord
{
    #[ORM\Id]
    #[ORM\Column(name: 'customer_id', length: 36)]
    private string $customerId;

    /** @var Collection<int, SavedAddressRecord> */
    #[ORM\OneToMany(targetEntity: SavedAddressRecord::class, mappedBy: 'book', cascade: ['persist'], orphanRemoval: true)]
    #[ORM\OrderBy(['position' => 'ASC'])]
    private Collection $addresses;

    public function __construct()
    {
        $this->addresses = new ArrayCollection();
    }

    public static function fromDomain(AddressBook $book): self
    {
        $record = new self();
        $record->customerId = $book->customerId()->value();
        $record->syncFromDomain($book);

        return $record;
    }

    public function updateFromDomain(AddressBook $book): void
    {
        $this->syncFromDomain($book);
    }

    public function toDomain(): AddressBook
    {
        $addresses = $this->addresses->toArray();
        usort(
            $addresses,
            static fn (SavedAddressRecord $left, SavedAddressRecord $right): int => $left->position() <=> $right->position(),
        );

        return AddressBook::reconstitute(
            CustomerId::fromString($this->customerId),
            array_map(
                static fn (SavedAddressRecord $address): SavedAddress => $address->toDomain(),
                $addresses,
            ),
        );
    }

    private function syncFromDomain(AddressBook $book): void
    {
        $indexed = [];

        foreach ($this->addresses as $record) {
            $indexed[$record->addressId()] = $record;
        }

        $seen = [];

        foreach ($book->addresses() as $position => $address) {
            $key = $address->id()->value();
            $seen[$key] = true;
            $existing = $indexed[$key] ?? null;

            if ($existing === null) {
                $this->addresses->add(SavedAddressRecord::fromDomain($this, $address, $position));
                continue;
            }

            $existing->updateFromDomain($address, $position);
        }

        foreach ($this->addresses->toArray() as $record) {
            if (!isset($seen[$record->addressId()])) {
                $this->addresses->removeElement($record);
            }
        }
    }
}
