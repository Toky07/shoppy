<?php

declare(strict_types=1);

namespace App\User\Domain\Exception;

use App\Shared\Domain\Exception\InvalidValue;

final class InvalidUserEmail extends \InvalidArgumentException implements InvalidValue
{
    public function __construct()
    {
        parent::__construct('Email must be a valid email address.');
    }

    public function errorCode(): string
    {
        return 'invalid_user_email';
    }

    public function field(): string
    {
        return 'email';
    }
}
