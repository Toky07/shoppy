<?php

declare(strict_types=1);

namespace App\Media\Presentation\Controller;

use App\Auth\Presentation\Security\AuthorizationAttributes;
use App\Media\Application\Query\ListMediaByOwnerQuery;
use App\Media\Application\QueryHandler\ListMediaByOwnerQueryHandler;
use App\Media\Presentation\Request\ListMediaHttpRequest;
use App\Media\Presentation\Security\MediaListSubject;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final readonly class ListMediaController
{
    public function __construct(
        private ListMediaByOwnerQueryHandler $listMedia,
    ) {
    }

    #[Route('/media', methods: ['GET'])]
    #[IsGranted(AuthorizationAttributes::MEDIA_LIST, subject: 'mediaList')]
    public function __invoke(
        ListMediaHttpRequest $httpRequest,
        MediaListSubject $mediaList,
        Request $request,
    ): JsonResponse {
        $list = $this->listMedia->handle(new ListMediaByOwnerQuery(
            $httpRequest->ownerType,
            $httpRequest->ownerId,
        ));

        return new JsonResponse($list->toArray());
    }
}
