<?php

declare(strict_types=1);

namespace App\Order\Presentation\Controller;

use App\Auth\Presentation\Http\CurrentUser;
use App\Cart\Application\Command\CheckoutCartCommand;
use App\Cart\Application\CommandHandler\CheckoutCartCommandHandler;
use App\Order\Application\Query\GetOrderQuery;
use App\Order\Application\QueryHandler\GetOrderQueryHandler;
use App\Order\Presentation\Request\PlaceOrderHttpRequest;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Auth\Presentation\Security\AuthorizationAttributes;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final readonly class PlaceOrderController
{
    public function __construct(
        private CurrentUser $currentUser,
        private CheckoutCartCommandHandler $checkoutCart,
        private GetOrderQueryHandler $getOrder,
    ) {
    }

    #[Route('/orders', methods: ['POST'])]
    #[IsGranted(AuthorizationAttributes::IS_AUTHENTICATED)]
    public function __invoke(Request $request): JsonResponse
    {
        $httpRequest = PlaceOrderHttpRequest::fromPayload($request->toArray());

        $orderId = $this->checkoutCart->handle(new CheckoutCartCommand(
            $this->currentUser->id(),
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
