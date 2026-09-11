<?php

declare(strict_types=1);

namespace App\Payment\Presentation\Controller;

use App\Auth\Application\Query\AuthenticateTokenQuery;
use App\Auth\Application\QueryHandler\AuthenticateTokenQueryHandler;
use App\Auth\Presentation\Http\BearerToken;
use App\Payment\Application\Command\CompletePaymentCommand;
use App\Payment\Application\CommandHandler\CompletePaymentCommandHandler;
use App\Payment\Application\Query\GetPaymentByOrderQuery;
use App\Payment\Application\QueryHandler\GetPaymentByOrderQueryHandler;
use App\Payment\Presentation\Request\CompletePaymentHttpRequest;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final readonly class CompletePaymentController
{
    public function __construct(
        private AuthenticateTokenQueryHandler $authenticateToken,
        private CompletePaymentCommandHandler $completePayment,
        private GetPaymentByOrderQueryHandler $getPaymentByOrder,
    ) {
    }

    #[Route('/payments/complete', methods: ['POST'])]
    public function __invoke(Request $request): JsonResponse
    {
        $userId = $this->authenticateToken->handle(new AuthenticateTokenQuery(
            BearerToken::fromAuthorizationHeader($request->headers->get('Authorization')),
        ));

        $httpRequest = CompletePaymentHttpRequest::fromPayload($request->toArray());

        $this->completePayment->handle(new CompletePaymentCommand(
            orderId: $httpRequest->orderId,
            customerId: $userId->value(),
        ));

        $payment = $this->getPaymentByOrder->handle(new GetPaymentByOrderQuery(
            orderId: $httpRequest->orderId,
            customerId: $userId->value(),
        ));

        return new JsonResponse($payment->toArray());
    }
}
