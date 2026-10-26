<?php

declare(strict_types=1);

namespace App\Cart\Domain\Exception;

use App\Shared\Domain\Exception\ConflictException;

final class GuestCheckoutRequiresAccount extends \RuntimeException implements ConflictException
{
    public function __construct()
    {
        parent::__construct('An account already exists for this email.');
    }

    public function errorCode(): string
    {
        return 'account_required';
    }
}
