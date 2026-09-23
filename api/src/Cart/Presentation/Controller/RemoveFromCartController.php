<?php

declare(strict_types=1);

namespace App\Cart\Presentation\Controller;

use App\Auth\Presentation\Http\CurrentUser;
use App\Cart\Application\Command\RemoveFromCartCommand;
use App\Cart\Application\CommandHandler\RemoveFromCartCommandHandler;
use App\Cart\Application\Query\GetCartQuery;
use App\Cart\Application\QueryHandler\GetCartQueryHandler;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final readonly class RemoveFromCartController
{
    public function __construct(
        private CurrentUser $currentUser,
        private RemoveFromCartCommandHandler $removeFromCart,
        private GetCartQueryHandler $getCart,
    ) {
    }

    #[Route('/cart/items/{productId}', methods: ['DELETE'])]
    public function __invoke(string $productId, Request $request): JsonResponse
    {
        $userId = $this->currentUser->id();

        $variantId = $request->query->get('variantId');
        if ($variantId !== null && !is_string($variantId)) {
            $variantId = null;
        }

        $this->removeFromCart->handle(new RemoveFromCartCommand(
            customerId: $userId,
            productId: $productId,
            variantId: $variantId === '' ? null : $variantId,
        ));

        $cart = $this->getCart->handle(new GetCartQuery($userId));

        return new JsonResponse($cart->toArray());
    }
}
