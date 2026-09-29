<?php

declare(strict_types=1);

namespace App\Payment\Presentation\Controller;

use App\Auth\Domain\Exception\Forbidden;
use App\Auth\Presentation\Http\CurrentUser;
use App\Order\Application\Query\GetOrderQuery;
use App\Order\Application\QueryHandler\GetOrderQueryHandler;
use App\Payment\Application\Command\CompletePaymentCommand;
use App\Payment\Application\PaymentProviderPolicy;
use App\Payment\Application\CommandHandler\CompletePaymentCommandHandler;
use App\Payment\Application\Query\GetPaymentByOrderQuery;
use App\Payment\Application\QueryHandler\GetPaymentByOrderQueryHandler;
use App\Payment\Presentation\Request\CompletePaymentHttpRequest;
use App\Payment\Presentation\Security\PaymentAccessSubject;
use App\Payment\Presentation\Security\PaymentVoter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use App\Auth\Presentation\Security\AuthenticationAttributes;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

final readonly class CompletePaymentController
{
    public function __construct(
        private CurrentUser $currentUser,
        private AuthorizationCheckerInterface $authorizationChecker,
        private GetOrderQueryHandler $getOrder,
        private CompletePaymentCommandHandler $completePayment,
        private GetPaymentByOrderQueryHandler $getPaymentByOrder,
        private PaymentProviderPolicy $paymentPolicy,
    ) {
    }

    #[Route('/payments/complete', methods: ['POST'])]
    #[IsGranted(AuthenticationAttributes::IS_AUTHENTICATED)]
    public function __invoke(Request $request): JsonResponse
    {
        $userId = $this->currentUser->id();
        $this->paymentPolicy->assertLocalCompletionAllowed();

        $httpRequest = CompletePaymentHttpRequest::fromPayload($request->toArray());
        $order = $this->getOrder->handle(new GetOrderQuery($httpRequest->orderId));

        if (!$this->authorizationChecker->isGranted(
            PaymentVoter::ACCESS,
            new PaymentAccessSubject($order->customerId),
        )) {
            throw new Forbidden();
        }

        $this->completePayment->handle(new CompletePaymentCommand(
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
