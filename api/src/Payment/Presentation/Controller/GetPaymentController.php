<?php

declare(strict_types=1);

namespace App\Payment\Presentation\Controller;

use App\Auth\Presentation\Http\CurrentUser;
use App\Payment\Application\Query\GetPaymentByOrderQuery;
use App\Payment\Application\QueryHandler\GetPaymentByOrderQueryHandler;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final readonly class GetPaymentController
{
    public function __construct(
        private CurrentUser $currentUser,
        private GetPaymentByOrderQueryHandler $getPaymentByOrder,
    ) {
    }

    #[Route('/payments/by-order/{orderId}', methods: ['GET'])]
    public function __invoke(string $orderId, Request $request): JsonResponse
    {
        $userId = $this->currentUser->id();

        $payment = $this->getPaymentByOrder->handle(new GetPaymentByOrderQuery(
            orderId: $orderId,
            customerId: $userId,
        ));

        return new JsonResponse($payment->toArray());
    }
}
