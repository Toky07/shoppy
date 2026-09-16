<?php

declare(strict_types=1);

namespace App\Payment\Presentation\Controller;

use App\Auth\Application\Query\AuthenticateTokenQuery;
use App\Auth\Application\QueryHandler\AuthenticateTokenQueryHandler;
use App\Auth\Presentation\Http\BearerToken;
use App\Payment\Application\Command\StartCheckoutCommand;
use App\Payment\Application\CommandHandler\StartCheckoutCommandHandler;
use App\Payment\Application\Query\GetPaymentByOrderQuery;
use App\Payment\Application\QueryHandler\GetPaymentByOrderQueryHandler;
use App\Payment\Presentation\Request\StartCheckoutHttpRequest;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final readonly class StartCheckoutController
{
    public function __construct(
        private AuthenticateTokenQueryHandler $authenticateToken,
        private StartCheckoutCommandHandler $startCheckout,
        private GetPaymentByOrderQueryHandler $getPaymentByOrder,
    ) {
    }

    #[Route('/payments/checkout', methods: ['POST'])]
    public function __invoke(Request $request): JsonResponse
    {
        $userId = $this->authenticateToken->handle(new AuthenticateTokenQuery(
            BearerToken::fromAuthorizationHeader($request->headers->get('Authorization')),
        ));

        $httpRequest = StartCheckoutHttpRequest::fromPayload($request->toArray());

        $checkout = $this->startCheckout->handle(new StartCheckoutCommand(
            orderId: $httpRequest->orderId,
            customerId: $userId->value(),
            provider: $httpRequest->provider,
            successUrl: $httpRequest->successUrl,
            cancelUrl: $httpRequest->cancelUrl,
        ));

        $payment = $this->getPaymentByOrder->handle(new GetPaymentByOrderQuery(
            orderId: $httpRequest->orderId,
            customerId: $userId->value(),
        ));

        return new JsonResponse([
            'provider' => $checkout->provider,
            'status' => $payment->status,
            'completedImmediately' => $checkout->completedImmediately,
            'redirectUrl' => $checkout->redirectUrl,
        ]);
    }
}
