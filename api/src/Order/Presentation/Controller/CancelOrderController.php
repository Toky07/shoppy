<?php

declare(strict_types=1);

namespace App\Order\Presentation\Controller;

use App\Auth\Presentation\Http\CurrentUser;
use App\Auth\Presentation\Security\GrantChecker;
use App\Order\Application\Command\CancelOrderCommand;
use App\Order\Application\CommandHandler\CancelOrderCommandHandler;
use App\Order\Application\Query\GetOrderQuery;
use App\Order\Application\QueryHandler\GetOrderQueryHandler;
use App\Order\Presentation\Security\OrderAccessSubject;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use App\Auth\Presentation\Security\AuthorizationAttributes;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final readonly class CancelOrderController
{
    public function __construct(
        private CurrentUser $currentUser,
        private GrantChecker $grantChecker,
        private CancelOrderCommandHandler $cancelOrder,
        private GetOrderQueryHandler $getOrder,
    ) {
    }

    #[Route('/orders/{id}/cancel', methods: ['POST'])]
    #[IsGranted(AuthorizationAttributes::IS_AUTHENTICATED)]
    public function __invoke(string $id, Request $request): JsonResponse
    {
        $userId = $this->currentUser->id();
        $order = $this->getOrder->handle(new GetOrderQuery($id));

        $this->grantChecker->denyUnlessGranted(
            AuthorizationAttributes::ORDER_CANCEL,
            new OrderAccessSubject($order->customerId),
        );

        $this->cancelOrder->handle(new CancelOrderCommand(
            orderId: $id,
            customerId: $userId,
        ));

        $order = $this->getOrder->handle(new GetOrderQuery($id));

        return new JsonResponse($order->toArray());
    }
}
