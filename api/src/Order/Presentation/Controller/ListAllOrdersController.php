<?php

declare(strict_types=1);

namespace App\Order\Presentation\Controller;

use App\Auth\Presentation\Http\CurrentUser;
use App\Order\Application\Query\ListAllOrdersQuery;
use App\Order\Application\QueryHandler\ListAllOrdersQueryHandler;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final readonly class ListAllOrdersController
{
    public function __construct(
        private CurrentUser $currentUser,
        private ListAllOrdersQueryHandler $listAllOrders,
    ) {
    }

    #[Route('/admin/orders', methods: ['GET'])]
    public function __invoke(Request $request): JsonResponse
    {
        $this->currentUser->requireAdmin();

        $response = $this->listAllOrders->handle(new ListAllOrdersQuery(
            page: $request->query->getInt('page', ListAllOrdersQuery::DEFAULT_PAGE),
            limit: $request->query->getInt('limit', ListAllOrdersQuery::DEFAULT_LIMIT),
        ));

        return new JsonResponse($response->toArray());
    }
}
