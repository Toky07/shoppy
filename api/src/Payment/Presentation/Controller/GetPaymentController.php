<?php

declare(strict_types=1);

namespace App\Payment\Presentation\Controller;

use App\Auth\Application\Query\AuthenticateTokenQuery;
use App\Auth\Application\QueryHandler\AuthenticateTokenQueryHandler;
use App\Auth\Presentation\Http\BearerToken;
use App\Payment\Application\Query\GetPaymentByOrderQuery;
use App\Payment\Application\QueryHandler\GetPaymentByOrderQueryHandler;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final readonly class GetPaymentController
{
    public function __construct(
        private AuthenticateTokenQueryHandler $authenticateToken,
        private GetPaymentByOrderQueryHandler $getPaymentByOrder,
    ) {
    }

    #[Route('/payments/by-order/{orderId}', methods: ['GET'])]
    public function __invoke(string $orderId, Request $request): JsonResponse
    {
        $userId = $this->authenticateToken->handle(new AuthenticateTokenQuery(
            BearerToken::fromAuthorizationHeader($request->headers->get('Authorization')),
        ));

        $payment = $this->getPaymentByOrder->handle(new GetPaymentByOrderQuery(
            orderId: $orderId,
            customerId: $userId->value(),
        ));

        return new JsonResponse($payment->toArray());
    }
}
