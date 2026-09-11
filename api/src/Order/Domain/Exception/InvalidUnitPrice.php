<?php

declare(strict_types=1);

namespace App\Order\Domain\Exception;

use App\Shared\Domain\Exception\InvalidValue;

final class InvalidUnitPrice extends \InvalidArgumentException implements InvalidValue
{
    public function __construct()
    {
        parent::__construct('Unit price cannot be negative.');
    }

    public function errorCode(): string
    {
        return 'invalid_unit_price';
    }

    public function field(): string
    {
        return 'unitPriceCents';
    }
}
