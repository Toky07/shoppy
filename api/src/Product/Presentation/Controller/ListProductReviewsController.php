<?php

declare(strict_types=1);

namespace App\Product\Presentation\Controller;

use App\Auth\Presentation\Http\CurrentUser;
use App\Product\Application\Query\ListProductReviewsQuery;
use App\Product\Application\QueryHandler\ListProductReviewsQueryHandler;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final readonly class ListProductReviewsController
{
    public function __construct(
        private ListProductReviewsQueryHandler $listReviews,
        private CurrentUser $currentUser,
    ) {
    }

    #[Route('/products/{id}/reviews', methods: ['GET'])]
    public function __invoke(string $id): JsonResponse
    {
        return new JsonResponse($this->listReviews->handle(new ListProductReviewsQuery(
            $id,
            $this->currentUser->idOrNull(),
        ))->toArray());
    }
}
