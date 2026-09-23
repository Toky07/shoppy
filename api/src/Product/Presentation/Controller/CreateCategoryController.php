<?php

declare(strict_types=1);

namespace App\Product\Presentation\Controller;

use App\Auth\Presentation\Http\CurrentUser;
use App\Product\Application\Command\CreateCategoryCommand;
use App\Product\Application\CommandHandler\CreateCategoryCommandHandler;
use App\Product\Application\Response\CategoryResponse;
use App\Shared\Presentation\Exception\InvalidRequest;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final readonly class CreateCategoryController
{
    public function __construct(
        private CurrentUser $currentUser,
        private CreateCategoryCommandHandler $createCategory,
    ) {
    }

    #[Route('/categories', methods: ['POST'])]
    public function __invoke(Request $request): JsonResponse
    {
        $this->currentUser->requireAdmin();

        $payload = $request->toArray();
        if (!isset($payload['name']) || !is_string($payload['name'])) {
            throw InvalidRequest::of('This field is required.', 'name');
        }

        $category = $this->createCategory->handle(new CreateCategoryCommand($payload['name']));

        return new JsonResponse(CategoryResponse::fromCategory($category)->toArray(), Response::HTTP_CREATED);
    }
}
