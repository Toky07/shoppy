<?php

declare(strict_types=1);

namespace App\Favorite\Presentation\Controller;

use App\Auth\Presentation\Http\CurrentUser;
use App\Auth\Presentation\Security\AuthorizationAttributes;
use App\Favorite\Application\Query\GetFavoritesQuery;
use App\Favorite\Application\QueryHandler\GetFavoritesQueryHandler;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final readonly class GetFavoritesController
{
    public function __construct(
        private CurrentUser $currentUser,
        private GetFavoritesQueryHandler $getFavorites,
    ) {
    }

    #[Route('/favorites', methods: ['GET'])]
    #[IsGranted(AuthorizationAttributes::IS_AUTHENTICATED)]
    public function __invoke(): JsonResponse
    {
        $list = $this->getFavorites->handle(new GetFavoritesQuery($this->currentUser->id()));

        return new JsonResponse($list->toArray());
    }
}
