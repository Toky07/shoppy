<?php

declare(strict_types=1);

namespace App\Order\Domain\ValueObject;

use App\Order\Domain\Exception\InvalidPostalAddress;

final readonly class PostalAddress
{
    public const RECIPIENT_MAX = 80;
    public const LINE_MAX = 120;
    public const POSTAL_CODE_MAX = 12;
    public const CITY_MAX = 80;

    private function __construct(
        private string $recipient,
        private string $line1,
        private ?string $line2,
        private string $postalCode,
        private string $city,
        private string $country,
    ) {
    }

    public static function fromInput(
        string $recipient,
        string $line1,
        ?string $line2,
        string $postalCode,
        string $city,
        string $country,
        string $prefix,
    ): self {
        return new self(
            self::text($recipient, $prefix.'.recipient', 1, self::RECIPIENT_MAX, 'Recipient must be between 1 and 80 characters.'),
            self::text($line1, $prefix.'.line1', 1, self::LINE_MAX, 'Address line must be between 1 and 120 characters.'),
            self::optionalLine($line2, $prefix.'.line2'),
            self::normalizePostalCode($postalCode, $prefix.'.postalCode'),
            self::text($city, $prefix.'.city', 1, self::CITY_MAX, 'City must be between 1 and 80 characters.'),
            self::normalizeCountry($country, $prefix.'.country'),
        );
    }

    public function recipient(): string
    {
        return $this->recipient;
    }

    public function line1(): string
    {
        return $this->line1;
    }

    public function line2(): ?string
    {
        return $this->line2;
    }

    public function postalCode(): string
    {
        return $this->postalCode;
    }

    public function city(): string
    {
        return $this->city;
    }

    public function country(): string
    {
        return $this->country;
    }

    /**
     * @return array{recipient: string, line1: string, line2: string|null, postalCode: string, city: string, country: string}
     */
    public function toArray(): array
    {
        return [
            'recipient' => $this->recipient,
            'line1' => $this->line1,
            'line2' => $this->line2,
            'postalCode' => $this->postalCode,
            'city' => $this->city,
            'country' => $this->country,
        ];
    }

    private static function text(string $value, string $field, int $min, int $max, string $message): string
    {
        $normalized = trim($value);

        if (preg_match('/\R/u', $normalized) === 1 || mb_strlen($normalized) < $min || mb_strlen($normalized) > $max) {
            throw new InvalidPostalAddress($field, $message);
        }

        return $normalized;
    }

    private static function optionalLine(?string $value, string $field): ?string
    {
        if ($value === null) {
            return null;
        }

        $normalized = trim($value);

        if ($normalized === '') {
            return null;
        }

        return self::text($normalized, $field, 1, self::LINE_MAX, 'Address line must be between 1 and 120 characters.');
    }

    private static function normalizePostalCode(string $value, string $field): string
    {
        $normalized = trim($value);

        if (preg_match('/^[A-Za-z0-9][A-Za-z0-9 \-]{0,11}$/', $normalized) !== 1) {
            throw new InvalidPostalAddress($field, 'Postal code must be between 1 and 12 letters, digits, spaces or hyphens.');
        }

        return $normalized;
    }

    private static function normalizeCountry(string $value, string $field): string
    {
        $normalized = strtoupper(trim($value));

        if (preg_match('/^[A-Z]{2}$/', $normalized) !== 1) {
            throw new InvalidPostalAddress($field, 'Country must be a 2-letter code.');
        }

        return $normalized;
    }
}
