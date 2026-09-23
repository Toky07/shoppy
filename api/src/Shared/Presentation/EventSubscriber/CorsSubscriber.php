<?php

declare(strict_types=1);

namespace App\Shared\Presentation\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final readonly class CorsSubscriber implements EventSubscriberInterface
{
    public function __construct(private string $allowedOrigins)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onRequest', 30],
            KernelEvents::RESPONSE => ['onResponse', 0],
        ];
    }

    public function onRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest() || $event->getRequest()->getMethod() !== 'OPTIONS') {
            return;
        }

        $origin = $this->allowedOrigin($event->getRequest());

        if ($origin === null) {
            return;
        }

        $response = new Response('', Response::HTTP_NO_CONTENT);
        $this->apply($response, $origin);
        $event->setResponse($response);
    }

    public function onResponse(ResponseEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $origin = $this->allowedOrigin($event->getRequest());

        if ($origin === null) {
            return;
        }

        $this->apply($event->getResponse(), $origin);
    }

    private function apply(Response $response, string $origin): void
    {
        $response->headers->set('Access-Control-Allow-Origin', $origin);
        $response->headers->set('Access-Control-Allow-Credentials', 'true');
        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', 'Accept, Authorization, Content-Type');
        $response->headers->set('Vary', 'Origin');
    }

    private function allowedOrigin(Request $request): ?string
    {
        $origin = $request->headers->get('Origin');

        if (!is_string($origin) || $origin === '') {
            return null;
        }

        $normalized = rtrim(strtolower($origin), '/');

        foreach (explode(',', $this->allowedOrigins) as $allowed) {
            if (rtrim(strtolower(trim($allowed)), '/') === $normalized) {
                return $origin;
            }
        }

        return null;
    }
}
