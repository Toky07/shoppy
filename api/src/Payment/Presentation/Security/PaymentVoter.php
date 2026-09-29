<?php

declare(strict_types=1);

namespace App\Payment\Presentation\Security;

use App\Auth\Infrastructure\Security\AuthenticatedUser;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * Payment actions are limited to the paying customer (admins have no bypass).
 *
 * @extends Voter<string, PaymentAccessSubject>
 */
final class PaymentVoter extends Voter
{
    public const ACCESS = 'PAYMENT_ACCESS';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return $attribute === self::ACCESS && $subject instanceof PaymentAccessSubject;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof AuthenticatedUser) {
            return false;
        }

        return $user->id() === $subject->customerId;
    }
}
