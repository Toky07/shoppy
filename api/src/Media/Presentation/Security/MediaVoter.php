<?php

declare(strict_types=1);

namespace App\Media\Presentation\Security;

use App\Auth\Infrastructure\Security\AuthenticatedUser;
use App\Media\Domain\ValueObject\MediaOwnerType;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * @extends Voter<string, MediaListSubject>
 */
final class MediaVoter extends Voter
{
    public const LIST = 'MEDIA_LIST';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return $attribute === self::LIST && $subject instanceof MediaListSubject;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        if ($subject->ownerTypeAlias === MediaOwnerType::PRODUCT_ALIAS) {
            return true;
        }

        $user = $token->getUser();

        if (!$user instanceof AuthenticatedUser) {
            return false;
        }

        return $user->isAdmin();
    }
}
