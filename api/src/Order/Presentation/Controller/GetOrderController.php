<?php

declare(strict_types=1);

namespace App\Order\Presentation\Controller;

use App\Auth\Application\Query\AuthenticateTokenQuery;
use App\Auth\Application\Query\RequireSelfOrAdminQuery;
use App\Auth\Application\QueryHandler\AuthenticateTokenQueryHandler;
use App\Auth\Application\QueryHandler\RequireSelfOrAdminQueryHandler;
use App\Auth\Presentation\Http\BearerToken;
use App\Order\Application\Query\GetOrderQuery;
use App\Order\Application\QueryHandler\GetOrderQueryHandler;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final readonly class GetOrderController
{
    public function __construct(
        private AuthenticateTokenQueryHandler $authenticateToken,
        private RequireSelfOrAdminQueryHandler $requireSelfOrAdmin,
        private GetOrderQueryHandler $getOrder,
    ) {
    }

    #[Route('/orders/{id}', methods: ['GET'])]
    public function __invoke(string $id, Request $request): JsonResponse
    {
        $token = BearerToken::fromAuthorizationHeader($request->headers->get('Authorization'));
        $this->authenticateToken->handle(new AuthenticateTokenQuery($token));

        $order = $this->getOrder->handle(new GetOrderQuery($id));

        $this->requireSelfOrAdmin->handle(new RequireSelfOrAdminQuery($token, $order->customerId));

        return new JsonResponse($order->toArray());
    }
}
