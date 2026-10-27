<?php

declare(strict_types=1);

namespace App\Product\Presentation\Controller;

use App\Auth\Presentation\Security\AuthorizationAttributes;
use App\Product\Application\Command\RestoreProductReviewCommand;
use App\Product\Application\CommandHandler\RestoreProductReviewCommandHandler;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final readonly class RestoreProductReviewController
{
    public function __construct(
        private RestoreProductReviewCommandHandler $restoreReview,
    ) {
    }

    #[Route('/admin/reviews/{id}/restore', methods: ['POST'])]
    #[IsGranted(AuthorizationAttributes::ROLE_ADMIN)]
    public function __invoke(string $id): Response
    {
        $this->restoreReview->handle(new RestoreProductReviewCommand($id));

        return new Response(status: Response::HTTP_NO_CONTENT);
    }
}
