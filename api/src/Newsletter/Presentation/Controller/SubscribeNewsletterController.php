<?php

declare(strict_types=1);

namespace App\Newsletter\Presentation\Controller;

use App\Newsletter\Application\Command\SubscribeNewsletterCommand;
use App\Newsletter\Application\CommandHandler\SubscribeNewsletterCommandHandler;
use App\Newsletter\Presentation\Request\SubscribeNewsletterHttpRequest;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final readonly class SubscribeNewsletterController
{
    public function __construct(
        private SubscribeNewsletterCommandHandler $subscribe,
    ) {
    }

    #[Route('/newsletter', methods: ['POST'])]
    public function __invoke(Request $request): JsonResponse
    {
        $httpRequest = SubscribeNewsletterHttpRequest::fromPayload($request->toArray());
        $this->subscribe->handle(new SubscribeNewsletterCommand($httpRequest->email));

        return new JsonResponse(['status' => 'subscribed'], Response::HTTP_CREATED);
    }
}
