<?php

declare(strict_types=1);

namespace App\Address\Infrastructure\Persistence\Doctrine\Entity;

use App\Address\Domain\ValueObject\SavedAddress;
use App\Address\Domain\ValueObject\SavedAddressId;
use App\Order\Domain\ValueObject\PostalAddress;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'saved_addresses')]
class SavedAddressRecord
{
    #[ORM\Id]
    #[ORM\Column(length: 36)]
    private string $id;

    #[ORM\ManyToOne(targetEntity: AddressBookRecord::class, inversedBy: 'addresses')]
    #[ORM\JoinColumn(name: 'customer_id', referencedColumnName: 'customer_id', nullable: false, onDelete: 'CASCADE')]
    private AddressBookRecord $book;

    #[ORM\Column(length: 80)]
    private string $recipient;

    #[ORM\Column(length: 120)]
    private string $line1;

    #[ORM\Column(length: 120, nullable: true)]
    private ?string $line2 = null;

    #[ORM\Column(name: 'postal_code', length: 12)]
    private string $postalCode;

    #[ORM\Column(length: 80)]
    private string $city;

    #[ORM\Column(length: 2)]
    private string $country;

    #[ORM\Column(name: 'is_default')]
    private bool $isDefault = false;

    #[ORM\Column]
    private int $position;

    public static function fromDomain(AddressBookRecord $book, SavedAddress $address, int $position): self
    {
        $record = new self();
        $record->id = $address->id()->value();
        $record->book = $book;
        $record->updateFromDomain($address, $position);

        return $record;
    }

    public function updateFromDomain(SavedAddress $address, int $position): void
    {
        $postal = $address->postal();
        $this->recipient = $postal->recipient();
        $this->line1 = $postal->line1();
        $this->line2 = $postal->line2();
        $this->postalCode = $postal->postalCode();
        $this->city = $postal->city();
        $this->country = $postal->country();
        $this->isDefault = $address->isDefault();
        $this->position = $position;
    }

    public function addressId(): string
    {
        return $this->id;
    }

    public function position(): int
    {
        return $this->position;
    }

    public function toDomain(): SavedAddress
    {
        return new SavedAddress(
            SavedAddressId::fromString($this->id),
            PostalAddress::fromInput(
                $this->recipient,
                $this->line1,
                $this->line2,
                $this->postalCode,
                $this->city,
                $this->country,
                'address',
            ),
            $this->isDefault,
        );
    }
}
