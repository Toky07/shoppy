<?php

declare(strict_types=1);

namespace App\Cart\Presentation\Controller;

use App\Auth\Presentation\Http\CurrentUser;
use App\Cart\Application\Command\UpdateCartItemQuantityCommand;
use App\Cart\Application\CommandHandler\UpdateCartItemQuantityCommandHandler;
use App\Cart\Application\Query\GetCartQuery;
use App\Cart\Application\QueryHandler\GetCartQueryHandler;
use App\Cart\Presentation\Request\UpdateCartItemHttpRequest;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final readonly class UpdateCartItemController
{
    public function __construct(
        private CurrentUser $currentUser,
        private UpdateCartItemQuantityCommandHandler $updateCartItemQuantity,
        private GetCartQueryHandler $getCart,
    ) {
    }

    #[Route('/cart/items/{productId}', methods: ['PUT'])]
    public function __invoke(string $productId, Request $request): JsonResponse
    {
        $userId = $this->currentUser->id();

        $httpRequest = UpdateCartItemHttpRequest::fromPayload($request->toArray());

        $this->updateCartItemQuantity->handle(new UpdateCartItemQuantityCommand(
            customerId: $userId,
            productId: $productId,
            quantity: $httpRequest->quantity,
            variantId: $httpRequest->variantId,
        ));

        $cart = $this->getCart->handle(new GetCartQuery($userId));

        return new JsonResponse($cart->toArray());
    }
}
