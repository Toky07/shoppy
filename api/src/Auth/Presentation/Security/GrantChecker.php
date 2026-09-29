<?php

declare(strict_types=1);

namespace App\Auth\Presentation\Security;

use App\Auth\Domain\Exception\Forbidden;
use App\Auth\Presentation\Http\CurrentUser;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Throws domain auth exceptions when Symfony authorization checks fail.
 *
 * Use {@see denyUnlessGranted()} on routes already protected by {@see AuthorizationAttributes::IS_AUTHENTICATED}.
 * Use {@see denyUnlessGrantedOrRequireAuthentication()} when anonymous callers may hit the check (401 vs 403).
 */
final readonly class GrantChecker
{
    public function __construct(
        private AuthorizationCheckerInterface $authorizationChecker,
        private CurrentUser $currentUser,
    ) {
    }

    public function denyUnlessGranted(string $attribute, mixed $subject = null): void
    {
        if (!$this->authorizationChecker->isGranted($attribute, $subject)) {
            throw new Forbidden();
        }
    }

    public function denyUnlessGrantedOrRequireAuthentication(string $attribute, mixed $subject = null): void
    {
        if ($this->authorizationChecker->isGranted($attribute, $subject)) {
            return;
        }

        $this->currentUser->id();

        throw new Forbidden();
    }
}
