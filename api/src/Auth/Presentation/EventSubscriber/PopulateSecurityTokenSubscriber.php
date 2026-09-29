<?php

declare(strict_types=1);

namespace App\Auth\Presentation\EventSubscriber;

use App\Auth\Infrastructure\Security\AuthenticatedUser;
use App\Auth\Presentation\Http\CurrentUser;
use App\User\Domain\ValueObject\Role;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Http\Authenticator\Token\PostAuthenticationToken;

final readonly class PopulateSecurityTokenSubscriber implements EventSubscriberInterface
{
    public function __construct(private TokenStorageInterface $tokenStorage)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::REQUEST => ['onRequest', 7]];
    }

    public function onRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $userId = $request->attributes->get(CurrentUser::USER_ID);
        $roleValue = $request->attributes->get(CurrentUser::ROLE);
        $accessToken = $request->attributes->get(CurrentUser::TOKEN);

        if (!is_string($userId) || $userId === ''
            || !is_string($roleValue) || $roleValue === ''
            || !is_string($accessToken) || $accessToken === '') {
            return;
        }

        $user = AuthenticatedUser::fromDomainRole(
            $userId,
            Role::fromString($roleValue),
            $accessToken,
        );

        $this->tokenStorage->setToken(new PostAuthenticationToken(
            $user,
            'main',
            $user->getRoles(),
        ));
    }
}
