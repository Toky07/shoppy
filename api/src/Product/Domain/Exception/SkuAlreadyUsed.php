<?php

declare(strict_types=1);

namespace App\Product\Domain\Exception;

use App\Shared\Domain\Exception\InvalidValue;

final class SkuAlreadyUsed extends \InvalidArgumentException implements InvalidValue
{
    public function __construct()
    {
        parent::__construct('This SKU is already used.');
    }

    public function errorCode(): string
    {
        return 'sku_already_used';
    }

    public function field(): string
    {
        return 'sku';
    }
}
