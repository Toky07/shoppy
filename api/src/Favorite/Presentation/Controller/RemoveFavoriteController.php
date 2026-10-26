<?php

declare(strict_types=1);

namespace App\Favorite\Presentation\Controller;

use App\Auth\Presentation\Http\CurrentUser;
use App\Auth\Presentation\Security\AuthorizationAttributes;
use App\Favorite\Application\Command\RemoveFavoriteCommand;
use App\Favorite\Application\CommandHandler\RemoveFavoriteCommandHandler;
use App\Favorite\Application\Query\GetFavoritesQuery;
use App\Favorite\Application\QueryHandler\GetFavoritesQueryHandler;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final readonly class RemoveFavoriteController
{
    public function __construct(
        private CurrentUser $currentUser,
        private RemoveFavoriteCommandHandler $removeFavorite,
        private GetFavoritesQueryHandler $getFavorites,
    ) {
    }

    #[Route('/favorites/{productId}', methods: ['DELETE'])]
    #[IsGranted(AuthorizationAttributes::IS_AUTHENTICATED)]
    public function __invoke(string $productId): JsonResponse
    {
        $userId = $this->currentUser->id();
        $this->removeFavorite->handle(new RemoveFavoriteCommand($userId, $productId));
        $list = $this->getFavorites->handle(new GetFavoritesQuery($userId));

        return new JsonResponse($list->toArray());
    }
}
