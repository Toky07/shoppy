<?php

declare(strict_types=1);

namespace App\Cart\Presentation\Controller;

use App\Auth\Presentation\Http\CurrentUser;
use App\Cart\Application\Query\GetCartQuery;
use App\Cart\Application\QueryHandler\GetCartQueryHandler;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use App\Auth\Presentation\Security\AuthorizationAttributes;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final readonly class GetCartController
{
    public function __construct(
        private CurrentUser $currentUser,
        private GetCartQueryHandler $getCart,
    ) {
    }

    #[Route('/cart', methods: ['GET'])]
    #[IsGranted(AuthorizationAttributes::IS_AUTHENTICATED)]
    public function __invoke(): JsonResponse
    {
        $userId = $this->currentUser->id();

        $cart = $this->getCart->handle(new GetCartQuery($userId));

        return new JsonResponse($cart->toArray());
    }
}
