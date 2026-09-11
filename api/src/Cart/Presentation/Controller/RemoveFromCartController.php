<?php

declare(strict_types=1);

namespace App\Cart\Presentation\Controller;

use App\Auth\Application\Query\AuthenticateTokenQuery;
use App\Auth\Application\QueryHandler\AuthenticateTokenQueryHandler;
use App\Auth\Presentation\Http\BearerToken;
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
        private AuthenticateTokenQueryHandler $authenticateToken,
        private RemoveFromCartCommandHandler $removeFromCart,
        private GetCartQueryHandler $getCart,
    ) {
    }

    #[Route('/cart/items/{productId}', methods: ['DELETE'])]
    public function __invoke(string $productId, Request $request): JsonResponse
    {
        $userId = $this->authenticateToken->handle(new AuthenticateTokenQuery(
            BearerToken::fromAuthorizationHeader($request->headers->get('Authorization')),
        ));

        $this->removeFromCart->handle(new RemoveFromCartCommand(
            customerId: $userId->value(),
            productId: $productId,
        ));

        $cart = $this->getCart->handle(new GetCartQuery($userId->value()));

        return new JsonResponse($cart->toArray());
    }
}
