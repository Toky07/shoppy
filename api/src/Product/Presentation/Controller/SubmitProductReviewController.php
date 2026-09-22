<?php

declare(strict_types=1);

namespace App\Product\Presentation\Controller;

use App\Auth\Application\Query\AuthenticateTokenQuery;
use App\Auth\Application\QueryHandler\AuthenticateTokenQueryHandler;
use App\Auth\Presentation\Http\BearerToken;
use App\Product\Application\Command\SubmitProductReviewCommand;
use App\Product\Application\CommandHandler\SubmitProductReviewCommandHandler;
use App\Product\Application\Port\ReviewAuthors;
use App\Product\Application\Response\ProductReviewResponse;
use App\Product\Presentation\Request\SubmitProductReviewHttpRequest;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final readonly class SubmitProductReviewController
{
    public function __construct(
        private AuthenticateTokenQueryHandler $authenticateToken,
        private SubmitProductReviewCommandHandler $submitReview,
        private ReviewAuthors $reviewAuthors,
    ) {
    }

    #[Route('/products/{id}/reviews', methods: ['POST'])]
    public function __invoke(string $id, Request $request): JsonResponse
    {
        $authorId = $this->authenticateToken->handle(new AuthenticateTokenQuery(
            BearerToken::fromAuthorizationHeader($request->headers->get('Authorization')),
        ));
        $httpRequest = SubmitProductReviewHttpRequest::fromPayload($request->toArray());
        $review = $this->submitReview->handle(new SubmitProductReviewCommand(
            productId: $id,
            authorId: $authorId->value(),
            rating: $httpRequest->rating,
            body: $httpRequest->body,
        ));
        $labels = $this->reviewAuthors->labelsFor([$authorId->value()]);

        return new JsonResponse(ProductReviewResponse::fromReview(
            $review,
            $labels[$authorId->value()] ?? 'Client',
            true,
        )->toArray());
    }
}
