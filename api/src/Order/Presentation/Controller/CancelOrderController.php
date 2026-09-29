<?php

declare(strict_types=1);

namespace App\Order\Presentation\Controller;

use App\Auth\Domain\Exception\Forbidden;
use App\Auth\Presentation\Http\CurrentUser;
use App\Order\Application\Command\CancelOrderCommand;
use App\Order\Application\CommandHandler\CancelOrderCommandHandler;
use App\Order\Application\Query\GetOrderQuery;
use App\Order\Application\QueryHandler\GetOrderQueryHandler;
use App\Order\Presentation\Security\OrderAccessSubject;
use App\Order\Presentation\Security\OrderVoter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

final readonly class CancelOrderController
{
    public function __construct(
        private CurrentUser $currentUser,
        private AuthorizationCheckerInterface $authorizationChecker,
        private CancelOrderCommandHandler $cancelOrder,
        private GetOrderQueryHandler $getOrder,
    ) {
    }

    #[Route('/orders/{id}/cancel', methods: ['POST'])]
    public function __invoke(string $id, Request $request): JsonResponse
    {
        $userId = $this->currentUser->id();
        $order = $this->getOrder->handle(new GetOrderQuery($id));

        if (!$this->authorizationChecker->isGranted(
            OrderVoter::CANCEL,
            new OrderAccessSubject($order->customerId),
        )) {
            throw new Forbidden();
        }

        $this->cancelOrder->handle(new CancelOrderCommand(
            orderId: $id,
            customerId: $userId,
        ));

        $order = $this->getOrder->handle(new GetOrderQuery($id));

        return new JsonResponse($order->toArray());
    }
}
