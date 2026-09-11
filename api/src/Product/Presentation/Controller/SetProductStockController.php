<?php

declare(strict_types=1);

namespace App\Product\Presentation\Controller;

use App\Auth\Application\Query\RequireAdminQuery;
use App\Auth\Application\QueryHandler\RequireAdminQueryHandler;
use App\Auth\Presentation\Http\BearerToken;
use App\Product\Application\Command\SetProductStockCommand;
use App\Product\Application\CommandHandler\SetProductStockCommandHandler;
use App\Product\Application\Query\GetProductQuery;
use App\Product\Application\QueryHandler\GetProductQueryHandler;
use App\Product\Presentation\Request\SetProductStockHttpRequest;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final readonly class SetProductStockController
{
    public function __construct(
        private RequireAdminQueryHandler $requireAdmin,
        private SetProductStockCommandHandler $setProductStock,
        private GetProductQueryHandler $getProduct,
    ) {
    }

    #[Route('/products/{id}/stock', methods: ['PUT'])]
    public function __invoke(string $id, Request $request): JsonResponse
    {
        $this->requireAdmin->handle(new RequireAdminQuery(
            BearerToken::fromAuthorizationHeader($request->headers->get('Authorization')),
        ));

        $httpRequest = SetProductStockHttpRequest::fromPayload($request->toArray());

        $this->setProductStock->handle(new SetProductStockCommand(
            id: $id,
            stock: $httpRequest->stock,
        ));

        $product = $this->getProduct->handle(new GetProductQuery($id));

        return new JsonResponse($product->toArray());
    }
}
