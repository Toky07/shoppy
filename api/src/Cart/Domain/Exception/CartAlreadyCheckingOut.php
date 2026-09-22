<?php

declare(strict_types=1);

namespace App\Cart\Domain\Exception;

use App\Shared\Domain\Exception\ConflictException;

final class CartAlreadyCheckingOut extends \RuntimeException implements ConflictException
{
    public function __construct()
    {
        parent::__construct('This cart is already being checked out.');
    }

    public function errorCode(): string
    {
        return 'cart_already_checking_out';
    }
}
