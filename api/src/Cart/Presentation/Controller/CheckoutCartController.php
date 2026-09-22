<?php

declare(strict_types=1);

namespace App\Cart\Presentation\Controller;

use App\Auth\Application\Query\AuthenticateTokenQuery;
use App\Auth\Application\QueryHandler\AuthenticateTokenQueryHandler;
use App\Auth\Presentation\Http\BearerToken;
use App\Cart\Application\Command\CheckoutCartCommand;
use App\Cart\Application\CommandHandler\CheckoutCartCommandHandler;
use App\Cart\Presentation\Request\CheckoutCartHttpRequest;
use App\Order\Application\Query\GetOrderQuery;
use App\Order\Application\QueryHandler\GetOrderQueryHandler;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final readonly class CheckoutCartController
{
    public function __construct(
        private AuthenticateTokenQueryHandler $authenticateToken,
        private CheckoutCartCommandHandler $checkoutCart,
        private GetOrderQueryHandler $getOrder,
    ) {
    }

    #[Route('/cart/checkout', methods: ['POST'])]
    public function __invoke(Request $request): JsonResponse
    {
        $userId = $this->authenticateToken->handle(new AuthenticateTokenQuery(
            BearerToken::fromAuthorizationHeader($request->headers->get('Authorization')),
        ));

        $httpRequest = CheckoutCartHttpRequest::fromPayload($request->toArray());

        $orderId = $this->checkoutCart->handle(new CheckoutCartCommand(
            $userId->value(),
            $httpRequest->delivery->shippingAddress,
            $httpRequest->delivery->billingAddress,
            $httpRequest->delivery->shippingMethod,
        ));
        $order = $this->getOrder->handle(new GetOrderQuery($orderId->value()));

        return new JsonResponse(
            $order->toArray(),
            Response::HTTP_CREATED,
            ['Location' => '/orders/'.$orderId->value()],
        );
    }
}
