<?php

declare(strict_types=1);

namespace App\Cart\Domain\Exception;

use App\Shared\Domain\Exception\InvalidValue;

final class EmptyCart extends \InvalidArgumentException implements InvalidValue
{
    public function __construct()
    {
        parent::__construct('Cart cannot be empty.');
    }

    public function errorCode(): string
    {
        return 'empty_cart';
    }

    public function field(): string
    {
        return 'items';
    }
}
