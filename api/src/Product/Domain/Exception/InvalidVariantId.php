<?php

declare(strict_types=1);

namespace App\Product\Domain\Exception;

use App\Shared\Domain\Exception\InvalidValue;

final class InvalidVariantId extends \InvalidArgumentException implements InvalidValue
{
    public function __construct()
    {
        parent::__construct('Variant id must be a valid UUID.');
    }

    public function errorCode(): string
    {
        return 'invalid_variant_id';
    }

    public function field(): string
    {
        return 'variantId';
    }
}
