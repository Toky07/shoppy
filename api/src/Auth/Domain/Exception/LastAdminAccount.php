<?php

declare(strict_types=1);

namespace App\Auth\Domain\Exception;

use App\Shared\Domain\Exception\ConflictException;

final class LastAdminAccount extends \RuntimeException implements ConflictException
{
    public function __construct()
    {
        parent::__construct('The last admin account cannot be deleted.');
    }

    public function errorCode(): string
    {
        return 'last_admin_account';
    }
}
