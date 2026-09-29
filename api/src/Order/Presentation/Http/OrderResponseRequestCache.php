<?php

declare(strict_types=1);

namespace App\Order\Presentation\Http;

use App\Auth\Domain\Exception\Unauthenticated;
use App\Auth\Infrastructure\Security\AuthenticatedUser;
use App\Order\Application\Query\GetOrderQuery;
use App\Order\Application\QueryHandler\GetOrderQueryHandler;
use App\Order\Application\Response\OrderResponse;
use App\Order\Domain\Exception\OrderNotFound;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Loads an order once per HTTP request (keyed by order id).
 */
final readonly class OrderResponseRequestCache
{
    private const ATTRIBUTE = '_cached_order_responses';

    public function __construct(
        private GetOrderQueryHandler $getOrder,
        private TokenStorageInterface $tokenStorage,
    ) {
    }

    public function get(Request $request, string $orderId): OrderResponse
    {
        /** @var array<string, OrderResponse> $cache */
        $cache = $request->attributes->get(self::ATTRIBUTE, []);

        if (!isset($cache[$orderId])) {
            try {
                $cache[$orderId] = $this->getOrder->handle(new GetOrderQuery($orderId));
            } catch (OrderNotFound $exception) {
                if (!$this->isAuthenticated()) {
                    throw new Unauthenticated();
                }

                throw $exception;
            }

            $request->attributes->set(self::ATTRIBUTE, $cache);
        }

        return $cache[$orderId];
    }

    private function isAuthenticated(): bool
    {
        $user = $this->tokenStorage->getToken()?->getUser();

        return $user instanceof AuthenticatedUser;
    }
}
