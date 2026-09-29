<?php

declare(strict_types=1);

namespace App\Order\Presentation\Controller;

use App\Order\Application\Query\ListAllOrdersQuery;
use App\Order\Application\QueryHandler\ListAllOrdersQueryHandler;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final readonly class ListAllOrdersController
{
    public function __construct(
        private ListAllOrdersQueryHandler $listAllOrders,
    ) {
    }

    #[Route('/admin/orders', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function __invoke(Request $request): JsonResponse
    {
        $response = $this->listAllOrders->handle(new ListAllOrdersQuery(
            page: $request->query->getInt('page', ListAllOrdersQuery::DEFAULT_PAGE),
            limit: $request->query->getInt('limit', ListAllOrdersQuery::DEFAULT_LIMIT),
        ));

        return new JsonResponse($response->toArray());
    }
}
