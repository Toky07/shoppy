<?php

declare(strict_types=1);

namespace App\Order\Presentation\Controller;

use App\Auth\Application\Query\AuthenticateTokenQuery;
use App\Auth\Application\QueryHandler\AuthenticateTokenQueryHandler;
use App\Auth\Presentation\Http\BearerToken;
use App\Order\Application\Command\PlaceOrderCommand;
use App\Order\Application\Command\PlaceOrderLine;
use App\Order\Application\CommandHandler\PlaceOrderCommandHandler;
use App\Order\Application\Query\GetOrderQuery;
use App\Order\Application\QueryHandler\GetOrderQueryHandler;
use App\Order\Presentation\Request\PlaceOrderHttpRequest;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final readonly class PlaceOrderController
{
    public function __construct(
        private AuthenticateTokenQueryHandler $authenticateToken,
        private PlaceOrderCommandHandler $placeOrder,
        private GetOrderQueryHandler $getOrder,
    ) {
    }

    #[Route('/orders', methods: ['POST'])]
    public function __invoke(Request $request): JsonResponse
    {
        $userId = $this->authenticateToken->handle(new AuthenticateTokenQuery(
            BearerToken::fromAuthorizationHeader($request->headers->get('Authorization')),
        ));

        $httpRequest = PlaceOrderHttpRequest::fromPayload($request->toArray());

        $id = $this->placeOrder->handle(new PlaceOrderCommand(
            customerId: $userId->value(),
            items: array_map(
                static fn (array $item): PlaceOrderLine => new PlaceOrderLine(
                    $item['productId'],
                    $item['quantity'],
                ),
                $httpRequest->items,
            ),
            shippingAddress: $httpRequest->delivery->shippingAddress,
            billingAddress: $httpRequest->delivery->billingAddress,
            shippingMethod: $httpRequest->delivery->shippingMethod,
        ));

        $order = $this->getOrder->handle(new GetOrderQuery($id->value()));

        return new JsonResponse(
            $order->toArray(),
            Response::HTTP_CREATED,
            ['Location' => '/orders/'.$id->value()],
        );
    }
}
