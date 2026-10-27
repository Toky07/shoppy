<?php

declare(strict_types=1);

namespace App\Invoice\Application\Response;

use App\Invoice\Domain\Entity\InvoiceBranding;

final readonly class InvoiceBrandingResponse
{
    public function __construct(
        public ?string $logoUrl,
        public ?string $mimeType,
    ) {
    }

    public static function fromBranding(InvoiceBranding $branding): self
    {
        $logo = $branding->logo();

        return new self(
            $logo?->publicUrl(),
            $logo?->mimeType(),
        );
    }

    /**
     * @return array{logoUrl: string|null, mimeType: string|null}
     */
    public function toArray(): array
    {
        return [
            'logoUrl' => $this->logoUrl,
            'mimeType' => $this->mimeType,
        ];
    }
}
