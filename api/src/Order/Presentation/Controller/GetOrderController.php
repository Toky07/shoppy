<?php

declare(strict_types=1);

namespace App\Order\Presentation\Controller;

use App\Auth\Domain\Exception\Forbidden;
use App\Auth\Presentation\Http\CurrentUser;
use App\Order\Application\Query\GetOrderQuery;
use App\Order\Application\QueryHandler\GetOrderQueryHandler;
use App\Order\Presentation\Security\OrderAccessSubject;
use App\Order\Presentation\Security\OrderVoter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

final readonly class GetOrderController
{
    public function __construct(
        private CurrentUser $currentUser,
        private AuthorizationCheckerInterface $authorizationChecker,
        private GetOrderQueryHandler $getOrder,
    ) {
    }

    #[Route('/orders/{id}', methods: ['GET'])]
    public function __invoke(string $id, Request $request): JsonResponse
    {
        $this->currentUser->id();
        $order = $this->getOrder->handle(new GetOrderQuery($id));

        if (!$this->authorizationChecker->isGranted(
            OrderVoter::VIEW,
            new OrderAccessSubject($order->customerId),
        )) {
            throw new Forbidden();
        }

        return new JsonResponse($order->toArray());
    }
}
