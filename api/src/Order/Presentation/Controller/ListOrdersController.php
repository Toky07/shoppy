<?php

declare(strict_types=1);

namespace App\Order\Presentation\Controller;

use App\Auth\Presentation\Http\CurrentUser;
use App\Order\Application\Query\ListOrdersQuery;
use App\Order\Application\QueryHandler\ListOrdersQueryHandler;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use App\Auth\Presentation\Security\AuthenticationAttributes;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final readonly class ListOrdersController
{
    public function __construct(
        private CurrentUser $currentUser,
        private ListOrdersQueryHandler $listOrders,
    ) {
    }

    #[Route('/orders', methods: ['GET'])]
    #[IsGranted(AuthenticationAttributes::IS_AUTHENTICATED)]
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
