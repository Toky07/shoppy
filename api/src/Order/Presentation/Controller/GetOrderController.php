<?php

declare(strict_types=1);

namespace App\Order\Presentation\Controller;

use App\Auth\Presentation\Security\AuthorizationAttributes;
use App\Auth\Presentation\Security\GrantChecker;
use App\Order\Application\Query\GetOrderQuery;
use App\Order\Application\QueryHandler\GetOrderQueryHandler;
use App\Order\Presentation\Security\OrderAccessSubject;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final readonly class GetOrderController
{
    public function __construct(
        private GrantChecker $grantChecker,
        private GetOrderQueryHandler $getOrder,
    ) {
    }

    #[Route('/orders/{id}', methods: ['GET'])]
    #[IsGranted(AuthorizationAttributes::IS_AUTHENTICATED)]
    public function __invoke(string $id, Request $request): JsonResponse
    {
        $order = $this->getOrder->handle(new GetOrderQuery($id));

        $this->grantChecker->denyUnlessGranted(
            AuthorizationAttributes::ORDER_VIEW,
            new OrderAccessSubject($order->customerId),
        );

        return new JsonResponse($order->toArray());
    }
}
