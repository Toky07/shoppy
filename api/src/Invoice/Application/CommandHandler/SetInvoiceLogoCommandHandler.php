<?php

declare(strict_types=1);

namespace App\Invoice\Application\CommandHandler;

use App\Invoice\Application\Command\SetInvoiceLogoCommand;
use App\Invoice\Application\DetectedInvoiceLogoType;
use App\Invoice\Application\Port\InvoiceLogoStorage;
use App\Invoice\Domain\Repository\InvoiceBrandingRepository;
use App\Invoice\Domain\ValueObject\InvoiceLogo;
use App\Invoice\Domain\ValueObject\InvoiceLogoSize;

final readonly class SetInvoiceLogoCommandHandler
{
    public function __construct(
        private InvoiceBrandingRepository $branding,
        private InvoiceLogoStorage $storage,
    ) {
    }

    public function handle(SetInvoiceLogoCommand $command): InvoiceLogo
    {
        InvoiceLogoSize::fromInt(strlen($command->binaryContent));
        $type = DetectedInvoiceLogoType::fromBinary($command->binaryContent);
        $logo = InvoiceLogo::of(
            $this->storage->store($type->extension(), $command->binaryContent),
            $type->mimeType(),
        );

        $this->branding->save($this->branding->get()->withLogo($logo));

        return $logo;
    }
}
