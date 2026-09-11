<?php

declare(strict_types=1);

namespace App\Auth\Domain\Exception;

use App\Shared\Domain\Exception\InvalidValue;

final class InvalidHashedPassword extends \InvalidArgumentException implements InvalidValue
{
    public function __construct()
    {
        parent::__construct('Password hash cannot be empty.');
    }

    public function errorCode(): string
    {
        return 'invalid_hashed_password';
    }

    public function field(): string
    {
        return 'password';
    }
}
