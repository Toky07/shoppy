<?php

declare(strict_types=1);

namespace App\Order\Presentation\Security;

use App\Auth\Infrastructure\Security\AuthenticatedUser;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * @extends Voter<string, OrderAccessSubject>
 */
final class OrderVoter extends Voter
{
    public const VIEW = 'ORDER_VIEW';

    public const CANCEL = 'ORDER_CANCEL';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::VIEW, self::CANCEL], true)
            && $subject instanceof OrderAccessSubject;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof AuthenticatedUser) {
            return false;
        }

        $isOwner = $user->id() === $subject->customerId;

        return match ($attribute) {
            self::VIEW => $user->isAdmin() || $isOwner,
            self::CANCEL => $isOwner,
            default => false,
        };
    }
}
