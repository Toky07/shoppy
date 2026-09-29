<?php

declare(strict_types=1);

namespace App\User\Presentation\Security;

use App\Auth\Infrastructure\Security\AuthenticatedUser;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * @extends Voter<string, UserAccessSubject>
 */
final class UserVoter extends Voter
{
    public const VIEW = 'USER_VIEW';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return $attribute === self::VIEW && $subject instanceof UserAccessSubject;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof AuthenticatedUser) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        return $user->id() === $subject->userId;
    }
}
