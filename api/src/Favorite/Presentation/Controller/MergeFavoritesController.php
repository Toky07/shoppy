<?php

declare(strict_types=1);

namespace App\Favorite\Presentation\Controller;

use App\Auth\Presentation\Http\CurrentUser;
use App\Auth\Presentation\Security\AuthorizationAttributes;
use App\Favorite\Application\Command\MergeFavoritesCommand;
use App\Favorite\Application\CommandHandler\MergeFavoritesCommandHandler;
use App\Favorite\Application\Query\GetFavoritesQuery;
use App\Favorite\Application\QueryHandler\GetFavoritesQueryHandler;
use App\Favorite\Presentation\Request\MergeFavoritesHttpRequest;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final readonly class MergeFavoritesController
{
    public function __construct(
        private CurrentUser $currentUser,
        private MergeFavoritesCommandHandler $mergeFavorites,
        private GetFavoritesQueryHandler $getFavorites,
    ) {
    }

    #[Route('/favorites/merge', methods: ['POST'])]
    #[IsGranted(AuthorizationAttributes::IS_AUTHENTICATED)]
    public function __invoke(Request $request): JsonResponse
    {
        $userId = $this->currentUser->id();
        $httpRequest = MergeFavoritesHttpRequest::fromPayload($request->toArray());
        $this->mergeFavorites->handle(new MergeFavoritesCommand($userId, $httpRequest->productIds));
        $list = $this->getFavorites->handle(new GetFavoritesQuery($userId));

        return new JsonResponse($list->toArray());
    }
}
