<?php

declare(strict_types=1);

namespace App\Invoice\Presentation\Controller;

use App\Auth\Presentation\Security\AuthorizationAttributes;
use App\Invoice\Application\Query\GetInvoiceBrandingQuery;
use App\Invoice\Application\QueryHandler\GetInvoiceBrandingQueryHandler;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final readonly class GetInvoiceBrandingController
{
    public function __construct(private GetInvoiceBrandingQueryHandler $getInvoiceBranding)
    {
    }

    #[Route('/invoices/branding', methods: ['GET'])]
    #[IsGranted(AuthorizationAttributes::ROLE_ADMIN)]
    public function __invoke(): JsonResponse
    {
        return new JsonResponse($this->getInvoiceBranding->handle(new GetInvoiceBrandingQuery())->toArray());
    }
}
