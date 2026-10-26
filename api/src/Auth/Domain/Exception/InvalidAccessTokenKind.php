<?php

declare(strict_types=1);

namespace App\Auth\Domain\Exception;

use App\Shared\Domain\Exception\InvalidValue;

final class InvalidAccessTokenKind extends \InvalidArgumentException implements InvalidValue
{
    public function __construct()
    {
        parent::__construct('Access token kind is invalid.');
    }

    public function errorCode(): string
    {
        return 'invalid_access_token_kind';
    }

    public function field(): string
    {
        return 'token';
    }
}
