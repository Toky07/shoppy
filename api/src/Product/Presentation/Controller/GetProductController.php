<?php

declare(strict_types=1);

namespace App\Product\Presentation\Controller;

use App\Auth\Application\Query\RequireAdminQuery;
use App\Auth\Application\QueryHandler\RequireAdminQueryHandler;
use App\Auth\Domain\Exception\Forbidden;
use App\Auth\Domain\Exception\Unauthenticated;
use App\Auth\Presentation\Http\BearerToken;
use App\Product\Application\Query\GetProductQuery;
use App\Product\Application\QueryHandler\GetProductQueryHandler;
use App\Product\Domain\Exception\ProductNotFound;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final readonly class GetProductController
{
    public function __construct(
        private GetProductQueryHandler $getProduct,
        private RequireAdminQueryHandler $requireAdmin,
    ) {
    }

    #[Route('/products/{id}', methods: ['GET'])]
    public function __invoke(string $id, Request $request): JsonResponse
    {
        $product = $this->getProduct->handle(new GetProductQuery($id));

        if (!$product->published) {
            try {
                $this->requireAdmin->handle(new RequireAdminQuery(
                    BearerToken::fromAuthorizationHeader($request->headers->get('Authorization')),
                ));
            } catch (Unauthenticated|Forbidden) {
                throw new ProductNotFound($id);
            }
        }

        return new JsonResponse($product->toArray());
    }
}
