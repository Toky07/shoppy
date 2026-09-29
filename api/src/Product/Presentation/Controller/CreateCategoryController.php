<?php

declare(strict_types=1);

namespace App\Product\Presentation\Controller;

use App\Product\Application\Command\CreateCategoryCommand;
use App\Product\Application\CommandHandler\CreateCategoryCommandHandler;
use App\Product\Application\Response\CategoryResponse;
use App\Shared\Presentation\Exception\InvalidRequest;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final readonly class CreateCategoryController
{
    public function __construct(
        private CreateCategoryCommandHandler $createCategory,
    ) {
    }

    #[Route('/categories', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function __invoke(Request $request): JsonResponse
    {
        $payload = $request->toArray();
        if (!isset($payload['name']) || !is_string($payload['name'])) {
            throw InvalidRequest::of('This field is required.', 'name');
        }

        $category = $this->createCategory->handle(new CreateCategoryCommand($payload['name']));

        return new JsonResponse(CategoryResponse::fromCategory($category)->toArray(), Response::HTTP_CREATED);
    }
}
