<?php

declare(strict_types=1);

namespace App\Cart\Presentation\Controller;

use App\Auth\Application\Query\AuthenticateTokenQuery;
use App\Auth\Application\QueryHandler\AuthenticateTokenQueryHandler;
use App\Auth\Presentation\Http\BearerToken;
use App\Cart\Application\Command\MergeCartCommand;
use App\Cart\Application\CommandHandler\MergeCartCommandHandler;
use App\Cart\Application\Query\GetCartQuery;
use App\Cart\Application\QueryHandler\GetCartQueryHandler;
use App\Cart\Presentation\Request\MergeCartHttpRequest;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final readonly class MergeCartController
{
    public function __construct(
        private AuthenticateTokenQueryHandler $authenticateToken,
        private MergeCartCommandHandler $mergeCart,
        private GetCartQueryHandler $getCart,
    ) {
    }

    #[Route('/cart/merge', methods: ['POST'])]
    public function __invoke(Request $request): JsonResponse
    {
        $userId = $this->authenticateToken->handle(new AuthenticateTokenQuery(
            BearerToken::fromAuthorizationHeader($request->headers->get('Authorization')),
        ));

        $httpRequest = MergeCartHttpRequest::fromPayload($request->toArray());
        $this->mergeCart->handle(new MergeCartCommand($userId->value(), $httpRequest->items));
        $cart = $this->getCart->handle(new GetCartQuery($userId->value()));

        return new JsonResponse($cart->toArray());
    }
}
