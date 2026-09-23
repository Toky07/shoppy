<?php

declare(strict_types=1);

namespace App\Cart\Presentation\Controller;

use App\Auth\Presentation\Http\CurrentUser;
use App\Cart\Application\Query\GetCartQuery;
use App\Cart\Application\QueryHandler\GetCartQueryHandler;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final readonly class GetCartController
{
    public function __construct(
        private CurrentUser $currentUser,
        private GetCartQueryHandler $getCart,
    ) {
    }

    #[Route('/cart', methods: ['GET'])]
    public function __invoke(Request $request): JsonResponse
    {
        $userId = $this->currentUser->id();

        $cart = $this->getCart->handle(new GetCartQuery($userId));

        return new JsonResponse($cart->toArray());
    }
}
