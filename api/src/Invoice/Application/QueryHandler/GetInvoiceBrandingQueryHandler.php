<?php

declare(strict_types=1);

namespace App\Invoice\Application\QueryHandler;

use App\Invoice\Application\Query\GetInvoiceBrandingQuery;
use App\Invoice\Application\Response\InvoiceBrandingResponse;
use App\Invoice\Domain\Repository\InvoiceBrandingRepository;

final readonly class GetInvoiceBrandingQueryHandler
{
    public function __construct(private InvoiceBrandingRepository $branding)
    {
    }

    public function handle(GetInvoiceBrandingQuery $query): InvoiceBrandingResponse
    {
        return InvoiceBrandingResponse::fromBranding($this->branding->get());
    }
}
