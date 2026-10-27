<?php

declare(strict_types=1);

namespace App\Invoice\Domain\Entity;

use App\Invoice\Domain\ValueObject\InvoiceLogo;

final readonly class InvoiceBranding
{
    public function __construct(private ?InvoiceLogo $logo = null)
    {
    }

    public static function blank(): self
    {
        return new self(null);
    }

    public function withLogo(InvoiceLogo $logo): self
    {
        return new self($logo);
    }

    public function logo(): ?InvoiceLogo
    {
        return $this->logo;
    }
}
