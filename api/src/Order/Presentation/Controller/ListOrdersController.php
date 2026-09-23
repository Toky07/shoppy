<?php

declare(strict_types=1);

namespace App\Order\Presentation\Controller;

use App\Auth\Presentation\Http\CurrentUser;
use App\Order\Application\Query\ListOrdersQuery;
use App\Order\Application\QueryHandler\ListOrdersQueryHandler;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final readonly class ListOrdersController
{
    public function __construct(
        private CurrentUser $currentUser,
        private ListOrdersQueryHandler $listOrders,
    ) {
    }

    #[Route('/orders', methods: ['GET'])]
    public function __invoke(Request $request): JsonResponse
    {
        $userId = $this->currentUser->id();

        $response = $this->listOrders->handle(new ListOrdersQuery(
            customerId: $userId,
            page: $request->query->getInt('page', ListOrdersQuery::DEFAULT_PAGE),
            limit: $request->query->getInt('limit', ListOrdersQuery::DEFAULT_LIMIT),
        ));

        return new JsonResponse($response->toArray());
    }
}
