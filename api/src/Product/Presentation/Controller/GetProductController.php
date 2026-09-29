<?php

declare(strict_types=1);

namespace App\Product\Presentation\Controller;

use App\Auth\Domain\Exception\Forbidden;
use App\Auth\Domain\Exception\Unauthenticated;
use App\Auth\Presentation\Security\GrantChecker;
use App\Product\Application\Query\GetProductQuery;
use App\Product\Application\QueryHandler\GetProductQueryHandler;
use App\Product\Domain\Exception\ProductNotFound;
use App\Auth\Presentation\Security\AuthorizationAttributes;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final readonly class GetProductController
{
    public function __construct(
        private GetProductQueryHandler $getProduct,
        private GrantChecker $grantChecker,
    ) {
    }

    #[Route('/products/{id}', methods: ['GET'])]
    public function __invoke(string $id): JsonResponse
    {
        $product = $this->getProduct->handle(new GetProductQuery($id));

        if (!$product->published) {
            try {
                $this->grantChecker->denyUnlessGrantedOrRequireAuthentication(AuthorizationAttributes::PRODUCT_ADMIN);
            } catch (Unauthenticated|Forbidden) {
                throw new ProductNotFound($id);
            }
        }

        return new JsonResponse($product->toArray());
    }
}
