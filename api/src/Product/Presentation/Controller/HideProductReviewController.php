<?php

declare(strict_types=1);

namespace App\Product\Presentation\Controller;

use App\Auth\Presentation\Security\AuthorizationAttributes;
use App\Product\Application\Command\HideProductReviewCommand;
use App\Product\Application\CommandHandler\HideProductReviewCommandHandler;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final readonly class HideProductReviewController
{
    public function __construct(
        private HideProductReviewCommandHandler $hideReview,
    ) {
    }

    #[Route('/admin/reviews/{id}/hide', methods: ['POST'])]
    #[IsGranted(AuthorizationAttributes::ROLE_ADMIN)]
    public function __invoke(string $id): Response
    {
        $this->hideReview->handle(new HideProductReviewCommand($id));

        return new Response(status: Response::HTTP_NO_CONTENT);
    }
}
