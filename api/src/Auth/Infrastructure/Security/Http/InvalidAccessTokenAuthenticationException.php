<?php

declare(strict_types=1);

namespace App\Auth\Infrastructure\Security\Http;

use Symfony\Component\Security\Core\Exception\AuthenticationException;

final class InvalidAccessTokenAuthenticationException extends AuthenticationException
{
}
