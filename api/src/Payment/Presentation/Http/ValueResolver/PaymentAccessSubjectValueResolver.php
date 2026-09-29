<?php

declare(strict_types=1);

namespace App\Payment\Presentation\Http\ValueResolver;

use App\Order\Presentation\Http\OrderResponseRequestCache;
use App\Payment\Presentation\Security\PaymentAccessSubject;
use App\Shared\Presentation\Exception\InvalidRequest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

final readonly class PaymentAccessSubjectValueResolver implements ValueResolverInterface
{
    public function __construct(private OrderResponseRequestCache $orders)
    {
    }

    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        if ($argument->getType() !== PaymentAccessSubject::class) {
            return [];
        }

        $orderId = $this->orderIdFromRequest($request);

        if ($orderId === null) {
            throw InvalidRequest::of('This field is required.', 'orderId');
        }

        $order = $this->orders->get($request, $orderId);

        yield new PaymentAccessSubject($order->customerId);
    }

    private function orderIdFromRequest(Request $request): ?string
    {
        $routeOrderId = $request->attributes->get('orderId');

        if (is_string($routeOrderId) && $routeOrderId !== '') {
            return $routeOrderId;
        }

        if (!\in_array($request->getMethod(), ['POST', 'PUT', 'PATCH'], true)) {
            return null;
        }

        $payload = $request->toArray();
        $bodyOrderId = $payload['orderId'] ?? null;

        return is_string($bodyOrderId) && $bodyOrderId !== '' ? $bodyOrderId : null;
    }
}
