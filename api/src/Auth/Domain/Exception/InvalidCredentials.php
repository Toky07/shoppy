<?php

declare(strict_types=1);

namespace App\Auth\Domain\Exception;

use App\Shared\Domain\Exception\AuthenticationFailed;

final class InvalidCredentials extends \RuntimeException implements AuthenticationFailed
{
    public function __construct()
    {
        parent::__construct('The provided credentials are invalid.');
    }

    public function errorCode(): string
    {
        return 'invalid_credentials';
    }
}
