<?php

declare(strict_types=1);

namespace App\Invoice\Presentation\Controller;

use App\Auth\Presentation\Security\AuthorizationAttributes;
use App\Invoice\Application\Query\GetInvoicePdfByOrderQuery;
use App\Invoice\Application\QueryHandler\GetInvoicePdfByOrderQueryHandler;
use App\Order\Application\Response\OrderResponse;
use App\Order\Presentation\Security\OrderAccessSubject;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final readonly class GetInvoicePdfController
{
    public function __construct(private GetInvoicePdfByOrderQueryHandler $getInvoicePdfByOrder)
    {
    }

    #[Route('/invoices/by-order/{orderId}/pdf', methods: ['GET'])]
    #[IsGranted(AuthorizationAttributes::IS_AUTHENTICATED)]
    #[IsGranted(AuthorizationAttributes::ORDER_VIEW, subject: 'orderAccess')]
    public function __invoke(OrderResponse $order, OrderAccessSubject $orderAccess): Response
    {
        $file = $this->getInvoicePdfByOrder->handle(new GetInvoicePdfByOrderQuery($order->id));

        return new Response($file->content, Response::HTTP_OK, [
            'Content-Type' => $file->mimeType,
            'Content-Disposition' => 'inline; filename="'.$file->filename.'"',
        ]);
    }
}
