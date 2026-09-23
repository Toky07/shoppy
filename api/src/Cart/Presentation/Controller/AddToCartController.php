<?php

declare(strict_types=1);

namespace App\Cart\Presentation\Controller;

use App\Auth\Presentation\Http\CurrentUser;
use App\Cart\Application\Command\AddToCartCommand;
use App\Cart\Application\CommandHandler\AddToCartCommandHandler;
use App\Cart\Application\Query\GetCartQuery;
use App\Cart\Application\QueryHandler\GetCartQueryHandler;
use App\Cart\Presentation\Request\AddToCartHttpRequest;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final readonly class AddToCartController
{
    public function __construct(
        private CurrentUser $currentUser,
        private AddToCartCommandHandler $addToCart,
        private GetCartQueryHandler $getCart,
    ) {
    }

    #[Route('/cart/items', methods: ['POST'])]
    public function __invoke(Request $request): JsonResponse
    {
        $userId = $this->currentUser->id();

        $httpRequest = AddToCartHttpRequest::fromPayload($request->toArray());

        $this->addToCart->handle(new AddToCartCommand(
            customerId: $userId,
            productId: $httpRequest->productId,
            quantity: $httpRequest->quantity,
            variantId: $httpRequest->variantId,
        ));

        $cart = $this->getCart->handle(new GetCartQuery($userId));

        return new JsonResponse($cart->toArray());
    }
}
