<?php

declare(strict_types=1);

namespace App\Payment\Presentation\Controller;

use App\Auth\Domain\Exception\Forbidden;
use App\Auth\Presentation\Http\CurrentUser;
use App\Order\Application\Query\GetOrderQuery;
use App\Order\Application\QueryHandler\GetOrderQueryHandler;
use App\Payment\Application\Query\GetPaymentByOrderQuery;
use App\Payment\Application\QueryHandler\GetPaymentByOrderQueryHandler;
use App\Payment\Presentation\Security\PaymentAccessSubject;
use App\Payment\Presentation\Security\PaymentVoter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

final readonly class GetPaymentController
{
    public function __construct(
        private CurrentUser $currentUser,
        private AuthorizationCheckerInterface $authorizationChecker,
        private GetOrderQueryHandler $getOrder,
        private GetPaymentByOrderQueryHandler $getPaymentByOrder,
    ) {
    }

    #[Route('/payments/by-order/{orderId}', methods: ['GET'])]
    public function __invoke(string $orderId, Request $request): JsonResponse
    {
        $userId = $this->currentUser->id();
        $order = $this->getOrder->handle(new GetOrderQuery($orderId));

        if (!$this->authorizationChecker->isGranted(
            PaymentVoter::ACCESS,
            new PaymentAccessSubject($order->customerId),
        )) {
            throw new Forbidden();
        }

        $payment = $this->getPaymentByOrder->handle(new GetPaymentByOrderQuery(
            orderId: $orderId,
            customerId: $userId,
        ));

        return new JsonResponse($payment->toArray());
    }
}
