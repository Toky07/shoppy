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

    protected function supports(string $attribute, mixed $subject): bool
    {
        return $attribute === self::VIEW && $subject instanceof OrderAccessSubject;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof AuthenticatedUser) {
            return false;
        }

        if (in_array(AuthenticatedUser::ROLE_ADMIN, $user->getRoles(), true)) {
            return true;
        }

        return $user->id() === $subject->customerId;
    }
}
