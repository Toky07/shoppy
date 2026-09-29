<?php

declare(strict_types=1);

namespace App\Auth\Presentation\Security;

use Symfony\Component\Security\Core\Authorization\Voter\AuthenticatedVoter;

final class AuthenticationAttributes
{
    public const IS_AUTHENTICATED = AuthenticatedVoter::IS_AUTHENTICATED;

    private function __construct()
    {
    }
}
