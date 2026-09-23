<?php

declare(strict_types=1);

namespace App\Order\Presentation\Controller;

use App\Auth\Presentation\Http\CurrentUser;
use App\Order\Application\Query\GetOrderQuery;
use App\Order\Application\QueryHandler\GetOrderQueryHandler;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final readonly class GetOrderController
{
    public function __construct(
        private CurrentUser $currentUser,
        private GetOrderQueryHandler $getOrder,
    ) {
    }

    #[Route('/orders/{id}', methods: ['GET'])]
    public function __invoke(string $id, Request $request): JsonResponse
    {
        $this->currentUser->id();
        $order = $this->getOrder->handle(new GetOrderQuery($id));
        $this->currentUser->assertSelfOrAdmin($order->customerId);

        return new JsonResponse($order->toArray());
    }
}
