<?php

declare(strict_types=1);

namespace App\Auth\Domain\Exception;

use App\Shared\Domain\Exception\InvalidValue;

final class InvalidAccountToken extends \RuntimeException implements InvalidValue
{
    public function __construct()
    {
        parent::__construct('The account token is invalid or expired.');
    }

    public function errorCode(): string
    {
        return 'invalid_account_token';
    }

    public function field(): string
    {
        return 'token';
    }
}
