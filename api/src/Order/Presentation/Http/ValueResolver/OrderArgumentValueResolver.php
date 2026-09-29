<?php

declare(strict_types=1);

namespace App\Order\Presentation\Http\ValueResolver;

use App\Order\Application\Response\OrderResponse;
use App\Order\Presentation\Http\OrderResponseRequestCache;
use App\Order\Presentation\Security\OrderAccessSubject;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

final readonly class OrderArgumentValueResolver implements ValueResolverInterface
{
    public function __construct(private OrderResponseRequestCache $orders)
    {
    }

    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        $type = $argument->getType();

        if ($type !== OrderResponse::class && $type !== OrderAccessSubject::class) {
            return [];
        }

        $orderId = $request->attributes->get('id') ?? $request->attributes->get('orderId');

        if (!is_string($orderId) || $orderId === '') {
            return [];
        }

        $order = $this->orders->get($request, $orderId);

        if ($type === OrderResponse::class) {
            yield $order;

            return;
        }

        yield new OrderAccessSubject($order->customerId);
    }
}
