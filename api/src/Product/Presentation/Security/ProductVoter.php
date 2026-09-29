<?php

declare(strict_types=1);

namespace App\Product\Presentation\Security;

use App\Auth\Infrastructure\Security\AuthenticatedUser;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * Admin-only catalog actions (draft listing, unpublished product access).
 *
 * @extends Voter<string, null>
 */
final class ProductVoter extends Voter
{
    public const ADMIN = 'PRODUCT_ADMIN';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return $attribute === self::ADMIN && $subject === null;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof AuthenticatedUser) {
            return false;
        }

        return $user->isAdmin();
    }
}
