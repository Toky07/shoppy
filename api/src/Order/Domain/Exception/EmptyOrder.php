<?php

declare(strict_types=1);

namespace App\Order\Domain\Exception;

use App\Shared\Domain\Exception\InvalidValue;

final class EmptyOrder extends \InvalidArgumentException implements InvalidValue
{
    public function __construct()
    {
        parent::__construct('An order must contain at least one item.');
    }

    public function errorCode(): string
    {
        return 'empty_order';
    }

    public function field(): string
    {
        return 'items';
    }
}
