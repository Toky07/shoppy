<?php

declare(strict_types=1);

namespace App\Auth\Application\EventSubscriber;

use App\Auth\Domain\Repository\AccessTokenRepository;
use App\Shared\Application\Event\UserRoleChanged;
use App\User\Domain\ValueObject\UserId;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final readonly class RevokeTokensOnRoleChanged implements EventSubscriberInterface
{
    public function __construct(private AccessTokenRepository $accessTokens)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [UserRoleChanged::class => 'onRoleChanged'];
    }

    public function onRoleChanged(UserRoleChanged $event): void
    {
        $this->accessTokens->deleteByUserId(UserId::fromString($event->userId));
    }
}
