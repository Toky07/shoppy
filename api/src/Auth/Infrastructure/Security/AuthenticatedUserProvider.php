<?php

declare(strict_types=1);

namespace App\Auth\Infrastructure\Security;

use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * Users are resolved from access tokens during the HTTP request; this provider exists
 * so Symfony Security can refresh/rehydrate the in-request {@see AuthenticatedUser}.
 *
 * @implements UserProviderInterface<AuthenticatedUser>
 */
final class AuthenticatedUserProvider implements UserProviderInterface
{
    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        throw new UserNotFoundException('Users are authenticated via access token per request.');
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
        if (!$user instanceof AuthenticatedUser) {
            throw new UnsupportedUserException(sprintf('Invalid user class "%s".', $user::class));
        }

        return $user;
    }

    public function supportsClass(string $class): bool
    {
        return $class === AuthenticatedUser::class || is_subclass_of($class, AuthenticatedUser::class);
    }
}
