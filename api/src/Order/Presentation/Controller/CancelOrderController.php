<?php

declare(strict_types=1);

namespace App\Order\Presentation\Controller;

use App\Auth\Presentation\Http\CurrentUser;
use App\Auth\Presentation\Security\AuthorizationAttributes;
use App\Order\Application\Command\CancelOrderCommand;
use App\Order\Application\CommandHandler\CancelOrderCommandHandler;
use App\Order\Application\Query\GetOrderQuery;
use App\Order\Application\QueryHandler\GetOrderQueryHandler;
use App\Order\Application\Response\OrderResponse;
use App\Order\Presentation\Security\OrderAccessSubject;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final readonly class CancelOrderController
{
    public function __construct(
        private CurrentUser $currentUser,
        private CancelOrderCommandHandler $cancelOrder,
        private GetOrderQueryHandler $getOrder,
    ) {
    }

    #[Route('/orders/{id}/cancel', methods: ['POST'])]
    #[IsGranted(AuthorizationAttributes::IS_AUTHENTICATED)]
    #[IsGranted(AuthorizationAttributes::ORDER_CANCEL, subject: 'orderAccess')]
    public function __invoke(OrderResponse $order, OrderAccessSubject $orderAccess): JsonResponse
    {
        $this->cancelOrder->handle(new CancelOrderCommand(
            orderId: $order->id,
            customerId: $this->currentUser->id(),
        ));

        $updated = $this->getOrder->handle(new GetOrderQuery($order->id));

        return new JsonResponse($updated->toArray());
    }
}
