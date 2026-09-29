<?php

declare(strict_types=1);

namespace App\Payment\Presentation\Controller;

use App\Auth\Presentation\Http\CurrentUser;
use App\Auth\Presentation\Security\AuthorizationAttributes;
use App\Order\Application\Response\OrderResponse;
use App\Payment\Application\Query\GetPaymentByOrderQuery;
use App\Payment\Application\QueryHandler\GetPaymentByOrderQueryHandler;
use App\Payment\Presentation\Security\PaymentAccessSubject;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final readonly class GetPaymentController
{
    public function __construct(
        private CurrentUser $currentUser,
        private GetPaymentByOrderQueryHandler $getPaymentByOrder,
    ) {
    }

    #[Route('/payments/by-order/{orderId}', methods: ['GET'])]
    #[IsGranted(AuthorizationAttributes::IS_AUTHENTICATED)]
    #[IsGranted(AuthorizationAttributes::PAYMENT_ACCESS, subject: 'paymentAccess')]
    public function __invoke(OrderResponse $order, PaymentAccessSubject $paymentAccess): JsonResponse
    {
        $payment = $this->getPaymentByOrder->handle(new GetPaymentByOrderQuery(
            orderId: $order->id,
            customerId: $this->currentUser->id(),
        ));

        return new JsonResponse($payment->toArray());
    }
}
