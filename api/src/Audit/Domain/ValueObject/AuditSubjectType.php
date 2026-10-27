<?php

declare(strict_types=1);

namespace App\Audit\Domain\ValueObject;

use App\Audit\Domain\Exception\InvalidAuditSubject;

final readonly class AuditSubjectType
{
    public const USER = 'user';

    public const PRODUCT = 'product';

    public const ORDER = 'order';

    private function __construct(private string $value)
    {
    }

    public static function user(): self
    {
        return new self(self::USER);
    }

    public static function product(): self
    {
        return new self(self::PRODUCT);
    }

    public static function order(): self
    {
        return new self(self::ORDER);
    }

    public static function fromString(string $value): self
    {
        return match ($value) {
            self::USER => self::user(),
            self::PRODUCT => self::product(),
            self::ORDER => self::order(),
            default => throw new InvalidAuditSubject(),
        };
    }

    public function value(): string
    {
        return $this->value;
    }
}
