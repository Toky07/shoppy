<?php

declare(strict_types=1);

namespace App\Cart\Presentation\Controller;

use App\Auth\Application\Response\IssuedSession;
use App\Auth\Presentation\Http\CurrentUser;
use App\Auth\Presentation\Http\SessionCookie;
use App\Cart\Application\Command\CheckoutCartCommand;
use App\Cart\Application\Command\GuestCheckoutCommand;
use App\Cart\Application\CommandHandler\CheckoutCartCommandHandler;
use App\Cart\Application\CommandHandler\GuestCheckoutCommandHandler;
use App\Cart\Presentation\Request\CheckoutCartHttpRequest;
use App\Order\Application\Query\GetOrderQuery;
use App\Order\Application\QueryHandler\GetOrderQueryHandler;
use App\Shared\Presentation\Exception\InvalidRequest;
use App\User\Application\Query\GetUserQuery;
use App\User\Application\QueryHandler\GetUserQueryHandler;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final readonly class CheckoutCartController
{
    public function __construct(
        private CurrentUser $currentUser,
        private CheckoutCartCommandHandler $checkoutCart,
        private GuestCheckoutCommandHandler $guestCheckout,
        private GetOrderQueryHandler $getOrder,
        private GetUserQueryHandler $getUser,
        private SessionCookie $sessionCookie,
    ) {
    }

    #[Route('/cart/checkout', methods: ['POST'])]
    public function __invoke(Request $request): JsonResponse
    {
        $httpRequest = CheckoutCartHttpRequest::fromPayload($request->toArray());
        $userId = $this->currentUser->idOrNull();
        $session = null;

        if ($userId === null) {
            if ($httpRequest->email === null) {
                throw InvalidRequest::of('This field is required.', 'email');
            }

            if ($httpRequest->items === []) {
                throw InvalidRequest::of('This field is required.', 'items');
            }

            $guest = $this->guestCheckout->handle(new GuestCheckoutCommand(
                $httpRequest->email,
                $httpRequest->items,
                $httpRequest->delivery->shippingAddress,
                $httpRequest->delivery->billingAddress,
                $httpRequest->delivery->shippingMethod,
            ));
            $userId = $guest->session->userId->value();
            $orderId = $guest->orderId;
            $session = $guest->session;
        } else {
            $orderId = $this->checkoutCart->handle(new CheckoutCartCommand(
                $userId,
                $httpRequest->delivery->shippingAddress,
                $httpRequest->delivery->billingAddress,
                $httpRequest->delivery->shippingMethod,
            ));
        }

        $payload = $this->getOrder->handle(new GetOrderQuery($orderId->value()))->toArray();

        if ($session instanceof IssuedSession) {
            $payload['user'] = $this->getUser->handle(new GetUserQuery($userId))->toArray();
        }

        $response = new JsonResponse(
            $payload,
            Response::HTTP_CREATED,
            ['Location' => '/orders/'.$orderId->value()],
        );

        if ($session instanceof IssuedSession) {
            $this->sessionCookie->attach($response, $request, $session->accessToken, $session->refreshToken);
        }

        return $response;
    }
}
