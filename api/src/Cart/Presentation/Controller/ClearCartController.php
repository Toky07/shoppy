<?php

declare(strict_types=1);

namespace App\Cart\Presentation\Controller;

use App\Auth\Presentation\Http\CurrentUser;
use App\Cart\Application\Command\ClearCartCommand;
use App\Cart\Application\CommandHandler\ClearCartCommandHandler;
use App\Cart\Application\Query\GetCartQuery;
use App\Cart\Application\QueryHandler\GetCartQueryHandler;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final readonly class ClearCartController
{
    public function __construct(
        private CurrentUser $currentUser,
        private ClearCartCommandHandler $clearCart,
        private GetCartQueryHandler $getCart,
    ) {
    }

    #[Route('/cart', methods: ['DELETE'])]
    public function __invoke(Request $request): JsonResponse
    {
        $userId = $this->currentUser->id();

        $this->clearCart->handle(new ClearCartCommand($userId));

        $cart = $this->getCart->handle(new GetCartQuery($userId));

        return new JsonResponse($cart->toArray());
    }
}
