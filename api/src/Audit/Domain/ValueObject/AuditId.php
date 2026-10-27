<?php

declare(strict_types=1);

namespace App\Audit\Domain\ValueObject;

use App\Audit\Domain\Exception\InvalidAuditId;

final readonly class AuditId
{
    private const UUID_PATTERN = '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i';

    private function __construct(private string $value)
    {
    }

    public static function fromString(string $value): self
    {
        if (preg_match(self::UUID_PATTERN, $value) !== 1) {
            throw new InvalidAuditId();
        }

        return new self($value);
    }

    public static function generate(): self
    {
        $bytes = random_bytes(16);
        $ms = (int) floor(microtime(true) * 1000);
        $bytes[0] = chr(($ms >> 40) & 0xff);
        $bytes[1] = chr(($ms >> 32) & 0xff);
        $bytes[2] = chr(($ms >> 24) & 0xff);
        $bytes[3] = chr(($ms >> 16) & 0xff);
        $bytes[4] = chr(($ms >> 8) & 0xff);
        $bytes[5] = chr($ms & 0xff);
        $bytes[6] = chr((ord($bytes[6]) & 0x0f) | 0x70);
        $bytes[8] = chr((ord($bytes[8]) & 0x3f) | 0x80);
        $hex = bin2hex($bytes);

        return self::fromString(sprintf(
            '%s-%s-%s-%s-%s',
            substr($hex, 0, 8),
            substr($hex, 8, 4),
            substr($hex, 12, 4),
            substr($hex, 16, 4),
            substr($hex, 20, 12),
        ));
    }

    public function value(): string
    {
        return $this->value;
    }
}
