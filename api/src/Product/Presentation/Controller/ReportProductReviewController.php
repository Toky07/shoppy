<?php

declare(strict_types=1);

namespace App\Product\Presentation\Controller;

use App\Auth\Presentation\Http\CurrentUser;
use App\Auth\Presentation\Security\AuthorizationAttributes;
use App\Product\Application\Command\ReportProductReviewCommand;
use App\Product\Application\CommandHandler\ReportProductReviewCommandHandler;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final readonly class ReportProductReviewController
{
    public function __construct(
        private CurrentUser $currentUser,
        private ReportProductReviewCommandHandler $reportReview,
    ) {
    }

    #[Route('/products/{id}/reviews/{reviewId}/report', methods: ['POST'])]
    #[IsGranted(AuthorizationAttributes::IS_AUTHENTICATED)]
    public function __invoke(string $id, string $reviewId): Response
    {
        $this->reportReview->handle(new ReportProductReviewCommand(
            $id,
            $reviewId,
            $this->currentUser->id(),
        ));

        return new Response(status: Response::HTTP_NO_CONTENT);
    }
}
