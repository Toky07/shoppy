<?php

declare(strict_types=1);

namespace App\Auth\Domain\Exception;

use App\Shared\Domain\Exception\InvalidValue;

final class InvalidPassword extends \InvalidArgumentException implements InvalidValue
{
    public function __construct()
    {
        parent::__construct('Password must be between 8 and 4096 characters.');
    }

    public function errorCode(): string
    {
        return 'invalid_password';
    }

    public function field(): string
    {
        return 'password';
    }
}
