<?php

declare(strict_types=1);

namespace App\Payment\Presentation\Controller;

use App\Payment\Application\Command\HandleStripeWebhookCommand;
use App\Payment\Application\CommandHandler\HandleStripeWebhookCommandHandler;
use App\Payment\Application\StripeWebhookParser;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final readonly class StripeWebhookController
{
    public function __construct(
        private StripeWebhookParser $parser,
        private HandleStripeWebhookCommandHandler $handleStripeWebhook,
    ) {
    }

    #[Route('/payments/webhooks/stripe', methods: ['POST'])]
    public function __invoke(Request $request): JsonResponse
    {
        $event = $this->parser->parse(
            $request->getContent(),
            (string) $request->headers->get('Stripe-Signature', ''),
        );

        $this->handleStripeWebhook->handle(new HandleStripeWebhookCommand(
            type: $event->type,
            providerReference: $event->sessionId,
        ));

        return new JsonResponse(['received' => true]);
    }
}
