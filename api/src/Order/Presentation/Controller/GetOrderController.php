<?php

declare(strict_types=1);

namespace App\Order\Presentation\Controller;

use App\Auth\Presentation\Security\AuthorizationAttributes;
use App\Order\Application\Response\OrderResponse;
use App\Order\Presentation\Security\OrderAccessSubject;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final readonly class GetOrderController
{
    #[Route('/orders/{id}', methods: ['GET'])]
    #[IsGranted(AuthorizationAttributes::IS_AUTHENTICATED)]
    #[IsGranted(AuthorizationAttributes::ORDER_VIEW, subject: 'orderAccess')]
    public function __invoke(OrderResponse $order, OrderAccessSubject $orderAccess): JsonResponse
    {
        return new JsonResponse($order->toArray());
    }
}
