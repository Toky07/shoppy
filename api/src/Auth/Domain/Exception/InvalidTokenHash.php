<?php

declare(strict_types=1);

namespace App\Auth\Domain\Exception;

use App\Shared\Domain\Exception\InvalidValue;

final class InvalidTokenHash extends \InvalidArgumentException implements InvalidValue
{
    public function __construct()
    {
        parent::__construct('Token hash cannot be empty.');
    }

    public function errorCode(): string
    {
        return 'invalid_token_hash';
    }

    public function field(): string
    {
        return 'token';
    }
}
