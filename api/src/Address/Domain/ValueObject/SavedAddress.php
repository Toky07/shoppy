<?php

declare(strict_types=1);

namespace App\Address\Domain\ValueObject;

use App\Order\Domain\ValueObject\PostalAddress;

final readonly class SavedAddress
{
    public function __construct(
        private SavedAddressId $id,
        private PostalAddress $postal,
        private bool $default,
    ) {
    }

    public static function capture(PostalAddress $postal, bool $default): self
    {
        return new self(SavedAddressId::generate(), $postal, $default);
    }

    public function id(): SavedAddressId
    {
        return $this->id;
    }

    public function postal(): PostalAddress
    {
        return $this->postal;
    }

    public function isDefault(): bool
    {
        return $this->default;
    }

    public function matches(PostalAddress $other): bool
    {
        return $this->postal->equals($other);
    }

    public function withDefault(bool $default): self
    {
        return new self($this->id, $this->postal, $default);
    }

    /**
     * @return array{id: string, recipient: string, line1: string, line2: string|null, postalCode: string, city: string, country: string, isDefault: bool}
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id->value(),
            ...$this->postal->toArray(),
            'isDefault' => $this->default,
        ];
    }
}
