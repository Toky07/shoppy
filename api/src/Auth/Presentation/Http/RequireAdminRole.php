<?php

declare(strict_types=1);

namespace App\Auth\Presentation\Http;

use App\Auth\Domain\Exception\Forbidden;
use App\Auth\Infrastructure\Security\AuthenticatedUser;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Ensures the current request is authenticated as an administrator (401 / 403).
 */
final readonly class RequireAdminRole
{
    public function __construct(
        private CurrentUser $currentUser,
        private AuthorizationCheckerInterface $authorizationChecker,
    ) {
    }

    public function invoke(): void
    {
        $this->currentUser->id();

        if (!$this->authorizationChecker->isGranted(AuthenticatedUser::ROLE_ADMIN)) {
            throw new Forbidden();
        }
    }
}
