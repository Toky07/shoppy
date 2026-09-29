<?php

declare(strict_types=1);

namespace App\Media\Presentation\Controller;

use App\Auth\Domain\Exception\Forbidden;
use App\Auth\Presentation\Http\CurrentUser;
use App\Media\Application\Query\ListMediaByOwnerQuery;
use App\Media\Application\QueryHandler\ListMediaByOwnerQueryHandler;
use App\Media\Presentation\Request\ListMediaHttpRequest;
use App\Media\Presentation\Security\MediaListSubject;
use App\Media\Presentation\Security\MediaVoter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

final readonly class ListMediaController
{
    public function __construct(
        private ListMediaByOwnerQueryHandler $listMedia,
        private CurrentUser $currentUser,
        private AuthorizationCheckerInterface $authorizationChecker,
    ) {
    }

    #[Route('/media', methods: ['GET'])]
    public function __invoke(Request $request): JsonResponse
    {
        $httpRequest = ListMediaHttpRequest::fromRequest($request);
        $subject = new MediaListSubject($httpRequest->ownerType);

        if (!$this->authorizationChecker->isGranted(MediaVoter::LIST, $subject)) {
            $this->currentUser->id();

            throw new Forbidden();
        }

        $list = $this->listMedia->handle(new ListMediaByOwnerQuery(
            $httpRequest->ownerType,
            $httpRequest->ownerId,
        ));

        return new JsonResponse($list->toArray());
    }
}
