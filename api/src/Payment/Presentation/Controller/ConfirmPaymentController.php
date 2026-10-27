<?php

declare(strict_types=1);

namespace App\Payment\Presentation\Controller;

use App\Auth\Presentation\Http\CurrentUser;
use App\Auth\Presentation\Security\AuthorizationAttributes;
use App\Payment\Application\Command\ConfirmPaymentCommand;
use App\Payment\Application\CommandHandler\ConfirmPaymentCommandHandler;
use App\Payment\Application\Query\GetPaymentByOrderQuery;
use App\Payment\Application\QueryHandler\GetPaymentByOrderQueryHandler;
use App\Payment\Presentation\Request\ConfirmPaymentHttpRequest;
use App\Payment\Presentation\Security\PaymentAccessSubject;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final readonly class ConfirmPaymentController
{
    public function __construct(
        private CurrentUser $currentUser,
        private ConfirmPaymentCommandHandler $confirmPayment,
        private GetPaymentByOrderQueryHandler $getPaymentByOrder,
    ) {
    }

    #[Route('/payments/confirm', methods: ['POST'])]
    #[IsGranted(AuthorizationAttributes::IS_AUTHENTICATED)]
    #[IsGranted(AuthorizationAttributes::PAYMENT_ACCESS, subject: 'paymentAccess')]
    public function __invoke(PaymentAccessSubject $paymentAccess, Request $request): JsonResponse
    {
        $userId = $this->currentUser->id();
        $httpRequest = ConfirmPaymentHttpRequest::fromPayload($request->toArray());

        $this->confirmPayment->handle(new ConfirmPaymentCommand(
            orderId: $httpRequest->orderId,
            customerId: $userId,
        ));

        $payment = $this->getPaymentByOrder->handle(new GetPaymentByOrderQuery(
            orderId: $httpRequest->orderId,
            customerId: $userId,
        ));

        return new JsonResponse($payment->toArray());
    }
}
