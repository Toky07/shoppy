<?php

declare(strict_types=1);

namespace App\Invoice\Presentation\Controller;

use App\Auth\Presentation\Security\AuthorizationAttributes;
use App\Invoice\Application\Command\SetInvoiceLogoCommand;
use App\Invoice\Application\CommandHandler\SetInvoiceLogoCommandHandler;
use App\Invoice\Application\Response\InvoiceBrandingResponse;
use App\Invoice\Presentation\Request\SetInvoiceLogoHttpRequest;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final readonly class SetInvoiceLogoController
{
    public function __construct(private SetInvoiceLogoCommandHandler $setInvoiceLogo)
    {
    }

    #[Route('/invoices/branding/logo', methods: ['POST'])]
    #[IsGranted(AuthorizationAttributes::ROLE_ADMIN)]
    public function __invoke(Request $request): JsonResponse
    {
        $httpRequest = SetInvoiceLogoHttpRequest::fromRequest($request);
        $logo = $this->setInvoiceLogo->handle(new SetInvoiceLogoCommand($httpRequest->binaryContent));

        return new JsonResponse((new InvoiceBrandingResponse($logo->publicUrl(), $logo->mimeType()))->toArray());
    }
}
