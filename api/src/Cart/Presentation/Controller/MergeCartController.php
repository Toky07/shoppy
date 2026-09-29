<?php

declare(strict_types=1);

namespace App\Cart\Presentation\Controller;

use App\Auth\Presentation\Http\CurrentUser;
use App\Cart\Application\Command\MergeCartCommand;
use App\Cart\Application\CommandHandler\MergeCartCommandHandler;
use App\Cart\Application\Query\GetCartQuery;
use App\Cart\Application\QueryHandler\GetCartQueryHandler;
use App\Cart\Presentation\Request\MergeCartHttpRequest;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use App\Auth\Presentation\Security\AuthorizationAttributes;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final readonly class MergeCartController
{
    public function __construct(
        private CurrentUser $currentUser,
        private MergeCartCommandHandler $mergeCart,
        private GetCartQueryHandler $getCart,
    ) {
    }

    #[Route('/cart/merge', methods: ['POST'])]
    #[IsGranted(AuthorizationAttributes::IS_AUTHENTICATED)]
    public function __invoke(Request $request): JsonResponse
    {
        $userId = $this->currentUser->id();

        $httpRequest = MergeCartHttpRequest::fromPayload($request->toArray());
        $this->mergeCart->handle(new MergeCartCommand($userId, $httpRequest->items));
        $cart = $this->getCart->handle(new GetCartQuery($userId));

        return new JsonResponse($cart->toArray());
    }
}
