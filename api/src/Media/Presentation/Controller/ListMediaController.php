<?php

declare(strict_types=1);

namespace App\Media\Presentation\Controller;

use App\Media\Application\Query\ListMediaByOwnerQuery;
use App\Media\Application\QueryHandler\ListMediaByOwnerQueryHandler;
use App\Media\Presentation\Request\ListMediaHttpRequest;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final readonly class ListMediaController
{
    public function __construct(private ListMediaByOwnerQueryHandler $listMedia)
    {
    }

    #[Route('/media', methods: ['GET'])]
    public function __invoke(Request $request): JsonResponse
    {
        $httpRequest = ListMediaHttpRequest::fromRequest($request);

        $list = $this->listMedia->handle(new ListMediaByOwnerQuery(
            $httpRequest->ownerType,
            $httpRequest->ownerId,
        ));

        return new JsonResponse($list->toArray());
    }
}
