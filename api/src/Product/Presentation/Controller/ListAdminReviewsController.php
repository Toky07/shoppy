<?php

declare(strict_types=1);

namespace App\Product\Presentation\Controller;

use App\Auth\Presentation\Security\AuthorizationAttributes;
use App\Product\Application\Query\ListAdminReviewsQuery;
use App\Product\Application\QueryHandler\ListAdminReviewsQueryHandler;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final readonly class ListAdminReviewsController
{
    public function __construct(
        private ListAdminReviewsQueryHandler $listReviews,
    ) {
    }

    #[Route('/admin/reviews', methods: ['GET'])]
    #[IsGranted(AuthorizationAttributes::ROLE_ADMIN)]
    public function __invoke(Request $request): JsonResponse
    {
        $reported = $request->query->get('reported');

        return new JsonResponse($this->listReviews->handle(new ListAdminReviewsQuery(
            page: $request->query->getInt('page', ListAdminReviewsQuery::DEFAULT_PAGE),
            limit: $request->query->getInt('limit', ListAdminReviewsQuery::DEFAULT_LIMIT),
            reportedOnly: $reported === '1' || $reported === 'true',
        ))->toArray());
    }
}
