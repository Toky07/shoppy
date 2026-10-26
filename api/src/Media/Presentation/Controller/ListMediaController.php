<?php

declare(strict_types=1);

namespace App\Media\Presentation\Controller;

use App\Auth\Domain\Exception\Forbidden;
use App\Auth\Domain\Exception\Unauthenticated;
use App\Auth\Presentation\Security\AuthorizationAttributes;
use App\Auth\Presentation\Security\GrantChecker;
use App\Media\Application\Port\MediaOwnerVisibility;
use App\Media\Application\Query\ListMediaByOwnerQuery;
use App\Media\Application\QueryHandler\ListMediaByOwnerQueryHandler;
use App\Media\Presentation\Request\ListMediaHttpRequest;
use App\Media\Presentation\Security\MediaListSubject;
use App\Product\Domain\Exception\ProductNotFound;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final readonly class ListMediaController
{
    public function __construct(
        private ListMediaByOwnerQueryHandler $listMedia,
        private MediaOwnerVisibility $ownerVisibility,
        private GrantChecker $grantChecker,
    ) {
    }

    #[Route('/media', methods: ['GET'])]
    #[IsGranted(AuthorizationAttributes::MEDIA_LIST, subject: 'mediaList')]
    public function __invoke(ListMediaHttpRequest $httpRequest, MediaListSubject $mediaList): JsonResponse
    {
        $list = $this->listMedia->handle(new ListMediaByOwnerQuery(
            $httpRequest->ownerType,
            $httpRequest->ownerId,
        ));

        if (!$this->ownerVisibility->isPubliclyListable($httpRequest->ownerType, $httpRequest->ownerId)) {
            try {
                $this->grantChecker->denyUnlessGrantedOrRequireAuthentication(AuthorizationAttributes::PRODUCT_ADMIN);
            } catch (Unauthenticated|Forbidden) {
                throw new ProductNotFound($httpRequest->ownerId);
            }
        }

        return new JsonResponse($list->toArray());
    }
}
