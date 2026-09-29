<?php

declare(strict_types=1);

namespace App\Auth\Presentation\EventSubscriber;

use App\Auth\Infrastructure\Security\AuthenticatedUser;
use App\Auth\Presentation\Http\CurrentUser;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Keeps request attributes aligned with the Security token for code that reads {@see CurrentUser} constants.
 */
final readonly class SyncCurrentUserAttributesSubscriber implements EventSubscriberInterface
{
    public function __construct(private TokenStorageInterface $tokenStorage)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::REQUEST => ['onRequest', 0]];
    }

    public function onRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        if ($event->getRequest()->attributes->has(CurrentUser::USER_ID)) {
            return;
        }

        $user = $this->tokenStorage->getToken()?->getUser();

        if (!$user instanceof AuthenticatedUser) {
            return;
        }

        $request = $event->getRequest();
        $request->attributes->set(CurrentUser::USER_ID, $user->id());
        $request->attributes->set(CurrentUser::ROLE, $user->domainRole()->value());
        $request->attributes->set(CurrentUser::TOKEN, $user->accessToken());
    }
}
