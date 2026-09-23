<?php

declare(strict_types=1);

namespace App\Shared\Presentation\EventSubscriber;

use App\Shared\Infrastructure\Http\FileRateLimiter;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final readonly class RateLimitSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private FileRateLimiter $limiter,
        private int $loginLimit,
        private int $registerLimit,
        private int $checkoutLimit,
        private int $webhookLimit,
        private int $windowSeconds,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::REQUEST => ['onRequest', 20]];
    }

    public function onRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $limit = $this->limit($request->getMethod(), $request->getPathInfo());

        if ($limit === null) {
            return;
        }

        $this->limiter->hit($request->getMethod().' '.$request->getPathInfo(), $request->getClientIp() ?? 'unknown', $limit, $this->windowSeconds);
    }

    private function limit(string $method, string $path): ?int
    {
        if ($method !== 'POST') {
            return null;
        }

        return match ($path) {
            '/auth/login' => $this->loginLimit,
            '/users' => $this->registerLimit,
            '/payments/checkout', '/payments/complete', '/cart/checkout' => $this->checkoutLimit,
            '/payments/webhooks/stripe' => $this->webhookLimit,
            default => null,
        };
    }
}
