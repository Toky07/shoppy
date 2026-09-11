<?php

declare(strict_types=1);

namespace App\User\Domain\Exception;

use App\Shared\Domain\Exception\InvalidValue;

final class InvalidUserRole extends \InvalidArgumentException implements InvalidValue
{
    public function __construct()
    {
        parent::__construct('Role must be customer or admin.');
    }

    public function errorCode(): string
    {
        return 'invalid_user_role';
    }

    public function field(): string
    {
        return 'role';
    }
}
