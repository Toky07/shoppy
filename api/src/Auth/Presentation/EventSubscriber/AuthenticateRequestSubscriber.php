<?php

declare(strict_types=1);

namespace App\Auth\Presentation\EventSubscriber;

use App\Auth\Application\Query\AuthenticateTokenQuery;
use App\Auth\Application\QueryHandler\AuthenticateTokenQueryHandler;
use App\Auth\Domain\Exception\Unauthenticated;
use App\Auth\Presentation\Http\AccessCredential;
use App\Auth\Presentation\Http\CurrentUser;
use App\User\Domain\Repository\UserRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final readonly class AuthenticateRequestSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private AuthenticateTokenQueryHandler $authenticateToken,
        private UserRepository $users,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::REQUEST => ['onRequest', 8]];
    }

    public function onRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $token = AccessCredential::fromRequest($request);

        if ($token === null) {
            return;
        }

        try {
            $userId = $this->authenticateToken->handle(new AuthenticateTokenQuery($token));
        } catch (Unauthenticated) {
            return;
        }

        $user = $this->users->findById($userId);

        if ($user === null || $user->isDeleted()) {
            return;
        }

        $request->attributes->set(CurrentUser::USER_ID, $userId->value());
        $request->attributes->set(CurrentUser::ROLE, $user->role()->value());
        $request->attributes->set(CurrentUser::TOKEN, $token);
    }
}
