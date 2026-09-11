<?php

declare(strict_types=1);

namespace App\Cart\Presentation\Controller;

use App\Auth\Application\Query\AuthenticateTokenQuery;
use App\Auth\Application\QueryHandler\AuthenticateTokenQueryHandler;
use App\Auth\Presentation\Http\BearerToken;
use App\Cart\Application\Query\GetCartQuery;
use App\Cart\Application\QueryHandler\GetCartQueryHandler;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final readonly class GetCartController
{
    public function __construct(
        private AuthenticateTokenQueryHandler $authenticateToken,
        private GetCartQueryHandler $getCart,
    ) {
    }

    #[Route('/cart', methods: ['GET'])]
    public function __invoke(Request $request): JsonResponse
    {
        $userId = $this->authenticateToken->handle(new AuthenticateTokenQuery(
            BearerToken::fromAuthorizationHeader($request->headers->get('Authorization')),
        ));

        $cart = $this->getCart->handle(new GetCartQuery($userId->value()));

        return new JsonResponse($cart->toArray());
    }
}
