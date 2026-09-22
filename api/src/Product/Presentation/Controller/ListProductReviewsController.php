<?php

declare(strict_types=1);

namespace App\Product\Presentation\Controller;

use App\Auth\Application\Query\AuthenticateTokenQuery;
use App\Auth\Application\QueryHandler\AuthenticateTokenQueryHandler;
use App\Auth\Domain\Exception\Unauthenticated;
use App\Auth\Presentation\Http\BearerToken;
use App\Product\Application\Query\ListProductReviewsQuery;
use App\Product\Application\QueryHandler\ListProductReviewsQueryHandler;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final readonly class ListProductReviewsController
{
    public function __construct(
        private ListProductReviewsQueryHandler $listReviews,
        private AuthenticateTokenQueryHandler $authenticateToken,
    ) {
    }

    #[Route('/products/{id}/reviews', methods: ['GET'])]
    public function __invoke(string $id, Request $request): JsonResponse
    {
        return new JsonResponse($this->listReviews->handle(new ListProductReviewsQuery(
            $id,
            $this->viewerId($request),
        ))->toArray());
    }

    private function viewerId(Request $request): ?string
    {
        $header = $request->headers->get('Authorization');

        if ($header === null || trim($header) === '') {
            return null;
        }

        try {
            return $this->authenticateToken->handle(new AuthenticateTokenQuery(
                BearerToken::fromAuthorizationHeader($header),
            ))->value();
        } catch (Unauthenticated) {
            return null;
        }
    }
}
