<?php

declare(strict_types=1);

namespace App\Order\Presentation\Controller;

use App\Auth\Application\Query\AuthenticateTokenQuery;
use App\Auth\Application\QueryHandler\AuthenticateTokenQueryHandler;
use App\Auth\Presentation\Http\BearerToken;
use App\Order\Application\Query\ListOrdersQuery;
use App\Order\Application\QueryHandler\ListOrdersQueryHandler;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final readonly class ListOrdersController
{
    public function __construct(
        private AuthenticateTokenQueryHandler $authenticateToken,
        private ListOrdersQueryHandler $listOrders,
    ) {
    }

    #[Route('/orders', methods: ['GET'])]
    public function __invoke(Request $request): JsonResponse
    {
        $userId = $this->authenticateToken->handle(new AuthenticateTokenQuery(
            BearerToken::fromAuthorizationHeader($request->headers->get('Authorization')),
        ));

        $response = $this->listOrders->handle(new ListOrdersQuery(
            customerId: $userId->value(),
            page: $request->query->getInt('page', ListOrdersQuery::DEFAULT_PAGE),
            limit: $request->query->getInt('limit', ListOrdersQuery::DEFAULT_LIMIT),
        ));

        return new JsonResponse($response->toArray());
    }
}
