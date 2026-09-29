<?php

declare(strict_types=1);

namespace App\Media\Presentation\Controller;

use App\Auth\Presentation\Security\AuthorizationAttributes;
use App\Media\Application\Command\UploadMediaCommand;
use App\Media\Application\CommandHandler\UploadMediaCommandHandler;
use App\Media\Application\Query\GetMediaQuery;
use App\Media\Application\QueryHandler\GetMediaQueryHandler;
use App\Media\Presentation\Request\UploadMediaHttpRequest;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final readonly class UploadMediaController
{
    public function __construct(
        private UploadMediaCommandHandler $uploadMedia,
        private GetMediaQueryHandler $getMedia,
    ) {
    }

    #[Route('/media', methods: ['POST'])]
    #[IsGranted(AuthorizationAttributes::ROLE_ADMIN)]
    public function __invoke(Request $request): JsonResponse
    {
        $httpRequest = UploadMediaHttpRequest::fromRequest($request);
        $id = $this->uploadMedia->handle(new UploadMediaCommand(
            ownerType: $httpRequest->ownerType,
            ownerId: $httpRequest->ownerId,
            originalFilename: $httpRequest->originalFilename,
            mimeType: $httpRequest->mimeType,
            binaryContent: $httpRequest->binaryContent,
            position: $httpRequest->position,
        ));

        $media = $this->getMedia->handle(new GetMediaQuery($id->value()));

        return new JsonResponse(
            $media->toArray(),
            Response::HTTP_CREATED,
            ['Location' => '/media/'.$id->value()],
        );
    }
}
