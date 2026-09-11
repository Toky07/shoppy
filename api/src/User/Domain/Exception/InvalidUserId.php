<?php

declare(strict_types=1);

namespace App\User\Domain\Exception;

use App\Shared\Domain\Exception\InvalidValue;

final class InvalidUserId extends \InvalidArgumentException implements InvalidValue
{
    public function __construct()
    {
        parent::__construct('User id must be a valid UUID.');
    }

    public function errorCode(): string
    {
        return 'invalid_user_id';
    }

    public function field(): string
    {
        return 'id';
    }
}
