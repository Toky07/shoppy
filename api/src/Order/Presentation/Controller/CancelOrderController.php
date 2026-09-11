<?php

declare(strict_types=1);

namespace App\Order\Presentation\Controller;

use App\Auth\Application\Query\AuthenticateTokenQuery;
use App\Auth\Application\QueryHandler\AuthenticateTokenQueryHandler;
use App\Auth\Presentation\Http\BearerToken;
use App\Order\Application\Command\CancelOrderCommand;
use App\Order\Application\CommandHandler\CancelOrderCommandHandler;
use App\Order\Application\Query\GetOrderQuery;
use App\Order\Application\QueryHandler\GetOrderQueryHandler;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final readonly class CancelOrderController
{
    public function __construct(
        private AuthenticateTokenQueryHandler $authenticateToken,
        private CancelOrderCommandHandler $cancelOrder,
        private GetOrderQueryHandler $getOrder,
    ) {
    }

    #[Route('/orders/{id}/cancel', methods: ['POST'])]
    public function __invoke(string $id, Request $request): JsonResponse
    {
        $userId = $this->authenticateToken->handle(new AuthenticateTokenQuery(
            BearerToken::fromAuthorizationHeader($request->headers->get('Authorization')),
        ));

        $this->cancelOrder->handle(new CancelOrderCommand(
            orderId: $id,
            customerId: $userId->value(),
        ));

        $order = $this->getOrder->handle(new GetOrderQuery($id));

        return new JsonResponse($order->toArray());
    }
}
