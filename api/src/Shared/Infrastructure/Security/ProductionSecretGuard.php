<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Security;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final readonly class ProductionSecretGuard implements EventSubscriberInterface
{
    private const FORBIDDEN = [
        '',
        'change-me-in-production',
    ];

    public function __construct(
        private string $environment,
        private string $secret,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::REQUEST => ['onRequest', 512]];
    }

    public function onRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest() || $this->environment !== 'prod') {
            return;
        }

        if (in_array($this->secret, self::FORBIDDEN, true)) {
            throw new \RuntimeException('APP_SECRET must be a unique value in production.');
        }
    }
}
