<?php

declare(strict_types=1);

namespace App\Order\Domain\ValueObject;

use App\Order\Domain\Exception\InvalidShippingMethod;

final readonly class ShippingMethod
{
    public const STANDARD = 'standard';
    public const EXPRESS = 'express';
    public const STANDARD_FEE_CENTS = 490;
    public const EXPRESS_FEE_CENTS = 990;
    public const FREE_FROM_CENTS = 4900;

    private function __construct(
        private string $code,
        private string $label,
        private int $feeCents,
    ) {
    }

    public static function quote(string $code, int $merchandiseCents): self
    {
        return match ($code) {
            self::STANDARD => new self(
                self::STANDARD,
                'Standard',
                $merchandiseCents >= self::FREE_FROM_CENTS ? 0 : self::STANDARD_FEE_CENTS,
            ),
            self::EXPRESS => new self(self::EXPRESS, 'Express', self::EXPRESS_FEE_CENTS),
            default => throw new InvalidShippingMethod(),
        };
    }

    public static function reconstitute(string $code, string $label, int $feeCents): self
    {
        if ($code === '' || $label === '' || $feeCents < 0) {
            throw new InvalidShippingMethod();
        }

        return new self($code, $label, $feeCents);
    }

    public function code(): string
    {
        return $this->code;
    }

    public function label(): string
    {
        return $this->label;
    }

    public function feeCents(): int
    {
        return $this->feeCents;
    }

    /**
     * @return array{method: string, label: string, fee: array{cents: int, currency: string}}
     */
    public function toArray(string $currency): array
    {
        return [
            'method' => $this->code,
            'label' => $this->label,
            'fee' => [
                'cents' => $this->feeCents,
                'currency' => $currency,
            ],
        ];
    }
}
