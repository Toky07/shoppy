<?php

declare(strict_types=1);

namespace App\Auth\Domain\Exception;

use App\Auth\Domain\ValueObject\PlainPassword;
use App\Shared\Domain\Exception\InvalidValue;

final class InvalidPassword extends \InvalidArgumentException implements InvalidValue
{
    public function __construct()
    {
        parent::__construct(sprintf(
            'Password must be between %d and %d characters.',
            PlainPassword::MIN_LENGTH,
            PlainPassword::MAX_LENGTH,
        ));
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
