<?php

declare(strict_types=1);

namespace App\Payment\Presentation\Controller;

use App\Auth\Domain\Exception\Forbidden;
use App\Auth\Presentation\Http\CurrentUser;
use App\Order\Application\Query\GetOrderQuery;
use App\Order\Application\QueryHandler\GetOrderQueryHandler;
use App\Payment\Application\AllowedCheckoutUrl;
use App\Payment\Application\Command\StartCheckoutCommand;
use App\Payment\Application\PaymentProviderPolicy;
use App\Payment\Application\CommandHandler\StartCheckoutCommandHandler;
use App\Payment\Application\Query\GetPaymentByOrderQuery;
use App\Payment\Application\QueryHandler\GetPaymentByOrderQueryHandler;
use App\Payment\Presentation\Request\StartCheckoutHttpRequest;
use App\Payment\Presentation\Security\PaymentAccessSubject;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use App\Auth\Presentation\Security\AuthorizationAttributes;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

final readonly class StartCheckoutController
{
    public function __construct(
        private CurrentUser $currentUser,
        private AuthorizationCheckerInterface $authorizationChecker,
        private GetOrderQueryHandler $getOrder,
        private StartCheckoutCommandHandler $startCheckout,
        private GetPaymentByOrderQueryHandler $getPaymentByOrder,
        private PaymentProviderPolicy $paymentPolicy,
        private AllowedCheckoutUrl $allowedCheckoutUrl,
    ) {
    }

    #[Route('/payments/checkout', methods: ['POST'])]
    #[IsGranted(AuthorizationAttributes::IS_AUTHENTICATED)]
    public function __invoke(Request $request): JsonResponse
    {
        $userId = $this->currentUser->id();

        $httpRequest = StartCheckoutHttpRequest::fromPayload($request->toArray());
        $order = $this->getOrder->handle(new GetOrderQuery($httpRequest->orderId));

        if (!$this->authorizationChecker->isGranted(
            AuthorizationAttributes::PAYMENT_ACCESS,
            new PaymentAccessSubject($order->customerId),
        )) {
            throw new Forbidden();
        }

        $this->paymentPolicy->assertCheckoutAllowed();
        $this->allowedCheckoutUrl->assertAllowed($httpRequest->successUrl, 'successUrl');
        $this->allowedCheckoutUrl->assertAllowed($httpRequest->cancelUrl, 'cancelUrl');

        $checkout = $this->startCheckout->handle(new StartCheckoutCommand(
            orderId: $httpRequest->orderId,
            customerId: $userId,
            provider: $this->paymentPolicy->provider(),
            successUrl: $httpRequest->successUrl,
            cancelUrl: $httpRequest->cancelUrl,
        ));

        $payment = $this->getPaymentByOrder->handle(new GetPaymentByOrderQuery(
            orderId: $httpRequest->orderId,
            customerId: $userId,
        ));

        return new JsonResponse([
            'provider' => $checkout->provider,
            'status' => $payment->status,
            'completedImmediately' => $checkout->completedImmediately,
            'redirectUrl' => $checkout->redirectUrl,
        ]);
    }
}
