<?php

declare(strict_types=1);

namespace App\Address\Application\Response;

use App\Address\Domain\Entity\AddressBook;
use App\Address\Domain\ValueObject\SavedAddress;

final readonly class AddressBookResponse
{
    /**
     * @param list<array{id: string, recipient: string, line1: string, line2: string|null, postalCode: string, city: string, country: string, isDefault: bool}> $addresses
     */
    public function __construct(public array $addresses)
    {
    }

    public static function fromBook(?AddressBook $book): self
    {
        if ($book === null) {
            return new self([]);
        }

        return new self(array_map(
            static fn (SavedAddress $address): array => $address->toArray(),
            $book->addresses(),
        ));
    }

    /**
     * @return array{addresses: list<array{id: string, recipient: string, line1: string, line2: string|null, postalCode: string, city: string, country: string, isDefault: bool}>}
     */
    public function toArray(): array
    {
        return ['addresses' => $this->addresses];
    }
}
