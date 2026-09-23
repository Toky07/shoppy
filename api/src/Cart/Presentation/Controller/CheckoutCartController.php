<?php

declare(strict_types=1);

namespace App\Cart\Presentation\Controller;

use App\Auth\Presentation\Http\CurrentUser;
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
        private CurrentUser $currentUser,
        private CheckoutCartCommandHandler $checkoutCart,
        private GetOrderQueryHandler $getOrder,
    ) {
    }

    #[Route('/cart/checkout', methods: ['POST'])]
    public function __invoke(Request $request): JsonResponse
    {
        $userId = $this->currentUser->id();

        $httpRequest = CheckoutCartHttpRequest::fromPayload($request->toArray());

        $orderId = $this->checkoutCart->handle(new CheckoutCartCommand(
            $userId,
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
