<?php

declare(strict_types=1);

namespace App\Favorite\Presentation\Controller;

use App\Auth\Presentation\Http\CurrentUser;
use App\Auth\Presentation\Security\AuthorizationAttributes;
use App\Favorite\Application\Command\ClearFavoritesCommand;
use App\Favorite\Application\CommandHandler\ClearFavoritesCommandHandler;
use App\Favorite\Application\Query\GetFavoritesQuery;
use App\Favorite\Application\QueryHandler\GetFavoritesQueryHandler;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final readonly class ClearFavoritesController
{
    public function __construct(
        private CurrentUser $currentUser,
        private ClearFavoritesCommandHandler $clearFavorites,
        private GetFavoritesQueryHandler $getFavorites,
    ) {
    }

    #[Route('/favorites', methods: ['DELETE'])]
    #[IsGranted(AuthorizationAttributes::IS_AUTHENTICATED)]
    public function __invoke(): JsonResponse
    {
        $userId = $this->currentUser->id();
        $this->clearFavorites->handle(new ClearFavoritesCommand($userId));
        $list = $this->getFavorites->handle(new GetFavoritesQuery($userId));

        return new JsonResponse($list->toArray());
    }
}
