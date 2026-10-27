<?php

declare(strict_types=1);

namespace App\Address\Domain\Entity;

use App\Address\Domain\Exception\AddressBookFull;
use App\Address\Domain\Exception\SavedAddressNotFound;
use App\Address\Domain\ValueObject\CustomerId;
use App\Address\Domain\ValueObject\SavedAddress;
use App\Address\Domain\ValueObject\SavedAddressId;
use App\Order\Domain\ValueObject\PostalAddress;

final class AddressBook
{
    public const MAX_ADDRESSES = 10;

    /**
     * @param list<SavedAddress> $addresses
     */
    private function __construct(
        private CustomerId $customerId,
        private array $addresses,
    ) {
        $this->ensureDefault();
    }

    public static function empty(CustomerId $customerId): self
    {
        return new self($customerId, []);
    }

    /**
     * @param list<SavedAddress> $addresses
     */
    public static function reconstitute(CustomerId $customerId, array $addresses): self
    {
        return new self($customerId, array_values($addresses));
    }

    public function customerId(): CustomerId
    {
        return $this->customerId;
    }

    /**
     * @return list<SavedAddress>
     */
    public function addresses(): array
    {
        return $this->addresses;
    }

    public function default(): ?SavedAddress
    {
        foreach ($this->addresses as $address) {
            if ($address->isDefault()) {
                return $address;
            }
        }

        return $this->addresses[0] ?? null;
    }

    public function remember(PostalAddress $address, bool $asDefault = true): void
    {
        foreach ($this->addresses as $index => $saved) {
            if ($saved->matches($address)) {
                if ($asDefault) {
                    $this->markDefaultAt($index);
                }

                return;
            }
        }

        if (count($this->addresses) >= self::MAX_ADDRESSES) {
            throw new AddressBookFull();
        }

        $makeDefault = $asDefault || $this->addresses === [];
        $rest = $makeDefault ? $this->withoutDefault() : $this->addresses;
        $this->addresses = [SavedAddress::capture($address, $makeDefault), ...$rest];
    }

    public function remove(SavedAddressId $id): void
    {
        $this->addresses = array_values(array_filter(
            $this->addresses,
            static fn (SavedAddress $address): bool => !$address->id()->equals($id),
        ));
        $this->ensureDefault();
    }

    public function markDefault(SavedAddressId $id): void
    {
        $index = $this->indexOf($id);

        if ($index === null) {
            throw new SavedAddressNotFound($id);
        }

        $this->markDefaultAt($index);
    }

    private function markDefaultAt(int $index): void
    {
        $this->addresses = array_values(array_map(
            static fn (int $current, SavedAddress $address): SavedAddress => $address->withDefault($current === $index),
            array_keys($this->addresses),
            $this->addresses,
        ));
    }

    /**
     * @return list<SavedAddress>
     */
    private function withoutDefault(): array
    {
        return array_map(
            static fn (SavedAddress $address): SavedAddress => $address->withDefault(false),
            $this->addresses,
        );
    }

    private function ensureDefault(): void
    {
        if ($this->addresses === []) {
            return;
        }

        foreach ($this->addresses as $address) {
            if ($address->isDefault()) {
                return;
            }
        }

        $this->addresses[0] = $this->addresses[0]->withDefault(true);
    }

    private function indexOf(SavedAddressId $id): ?int
    {
        foreach ($this->addresses as $index => $address) {
            if ($address->id()->equals($id)) {
                return $index;
            }
        }

        return null;
    }
}
