<?php

declare(strict_types=1);

namespace App\Media\Presentation\Controller;

use App\Auth\Presentation\Http\CurrentUser;
use App\Media\Application\Command\ReorderMediaCommand;
use App\Media\Application\CommandHandler\ReorderMediaCommandHandler;
use App\Media\Presentation\Request\ReorderMediaHttpRequest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final readonly class ReorderMediaController
{
    public function __construct(
        private CurrentUser $currentUser,
        private ReorderMediaCommandHandler $reorderMedia,
    ) {
    }

    #[Route('/media/order', methods: ['PUT'])]
    public function __invoke(Request $request): Response
    {
        $this->currentUser->requireAdmin();

        $httpRequest = ReorderMediaHttpRequest::fromPayload($request->toArray());
        $this->reorderMedia->handle(new ReorderMediaCommand(
            $httpRequest->ownerType,
            $httpRequest->ownerId,
            $httpRequest->ids,
        ));

        return new Response(status: Response::HTTP_NO_CONTENT);
    }
}
