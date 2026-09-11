<?php

declare(strict_types=1);

namespace App\Cart\Presentation\Controller;

use App\Auth\Application\Query\AuthenticateTokenQuery;
use App\Auth\Application\QueryHandler\AuthenticateTokenQueryHandler;
use App\Auth\Presentation\Http\BearerToken;
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
        private AuthenticateTokenQueryHandler $authenticateToken,
        private UpdateCartItemQuantityCommandHandler $updateCartItemQuantity,
        private GetCartQueryHandler $getCart,
    ) {
    }

    #[Route('/cart/items/{productId}', methods: ['PUT'])]
    public function __invoke(string $productId, Request $request): JsonResponse
    {
        $userId = $this->authenticateToken->handle(new AuthenticateTokenQuery(
            BearerToken::fromAuthorizationHeader($request->headers->get('Authorization')),
        ));

        $httpRequest = UpdateCartItemHttpRequest::fromPayload($request->toArray());

        $this->updateCartItemQuantity->handle(new UpdateCartItemQuantityCommand(
            customerId: $userId->value(),
            productId: $productId,
            quantity: $httpRequest->quantity,
        ));

        $cart = $this->getCart->handle(new GetCartQuery($userId->value()));

        return new JsonResponse($cart->toArray());
    }
}
