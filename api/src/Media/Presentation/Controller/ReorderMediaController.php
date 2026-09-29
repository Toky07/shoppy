<?php

declare(strict_types=1);

namespace App\Media\Presentation\Controller;

use App\Media\Application\Command\ReorderMediaCommand;
use App\Media\Application\CommandHandler\ReorderMediaCommandHandler;
use App\Media\Presentation\Request\ReorderMediaHttpRequest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final readonly class ReorderMediaController
{
    public function __construct(
        private ReorderMediaCommandHandler $reorderMedia,
    ) {
    }

    #[Route('/media/order', methods: ['PUT'])]
    #[IsGranted('ROLE_ADMIN')]
    public function __invoke(Request $request): Response
    {
        $httpRequest = ReorderMediaHttpRequest::fromPayload($request->toArray());
        $this->reorderMedia->handle(new ReorderMediaCommand(
            $httpRequest->ownerType,
            $httpRequest->ownerId,
            $httpRequest->ids,
        ));

        return new Response(status: Response::HTTP_NO_CONTENT);
    }
}
