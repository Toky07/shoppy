<?php

declare(strict_types=1);

namespace App\Favorite\Presentation\Controller;

use App\Auth\Presentation\Http\CurrentUser;
use App\Auth\Presentation\Security\AuthorizationAttributes;
use App\Favorite\Application\Command\AddFavoriteCommand;
use App\Favorite\Application\CommandHandler\AddFavoriteCommandHandler;
use App\Favorite\Application\Query\GetFavoritesQuery;
use App\Favorite\Application\QueryHandler\GetFavoritesQueryHandler;
use App\Favorite\Presentation\Request\AddFavoriteHttpRequest;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final readonly class AddFavoriteController
{
    public function __construct(
        private CurrentUser $currentUser,
        private AddFavoriteCommandHandler $addFavorite,
        private GetFavoritesQueryHandler $getFavorites,
    ) {
    }

    #[Route('/favorites', methods: ['POST'])]
    #[IsGranted(AuthorizationAttributes::IS_AUTHENTICATED)]
    public function __invoke(Request $request): JsonResponse
    {
        $userId = $this->currentUser->id();
        $httpRequest = AddFavoriteHttpRequest::fromPayload($request->toArray());
        $this->addFavorite->handle(new AddFavoriteCommand($userId, $httpRequest->productId));
        $list = $this->getFavorites->handle(new GetFavoritesQuery($userId));

        return new JsonResponse($list->toArray());
    }
}
