<?php

declare(strict_types=1);

namespace App\Invoice\Infrastructure\Persistence\Doctrine\Entity;

use App\Invoice\Domain\Entity\InvoiceBranding;
use App\Invoice\Domain\ValueObject\InvoiceLogo;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'invoice_branding')]
class InvoiceBrandingRecord
{
    public const DEFAULT_ID = 'default';

    #[ORM\Id]
    #[ORM\Column(length: 16)]
    private string $id = self::DEFAULT_ID;

    #[ORM\Column(name: 'logo_relative_path', length: 255, nullable: true)]
    private ?string $logoRelativePath = null;

    #[ORM\Column(name: 'logo_mime_type', length: 32, nullable: true)]
    private ?string $logoMimeType = null;

    public static function fromDomain(InvoiceBranding $branding): self
    {
        $record = new self();
        $record->replaceFromDomain($branding);

        return $record;
    }

    public function replaceFromDomain(InvoiceBranding $branding): void
    {
        $this->id = self::DEFAULT_ID;
        $this->logoRelativePath = $branding->logo()?->relativePath();
        $this->logoMimeType = $branding->logo()?->mimeType();
    }

    public function toDomain(): InvoiceBranding
    {
        if ($this->logoRelativePath === null || $this->logoMimeType === null) {
            return InvoiceBranding::blank();
        }

        return InvoiceBranding::blank()->withLogo(
            InvoiceLogo::of($this->logoRelativePath, $this->logoMimeType),
        );
    }
}
