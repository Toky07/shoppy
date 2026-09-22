<?php

declare(strict_types=1);

namespace App\Product\Domain\Exception;

use App\Shared\Domain\Exception\InvalidValue;

final class InvalidVariantLabel extends \InvalidArgumentException implements InvalidValue
{
    public function __construct(private string $fieldName)
    {
        parent::__construct('This value must be a short label.');
    }

    public function errorCode(): string
    {
        return 'invalid_variant_label';
    }

    public function field(): string
    {
        return $this->fieldName;
    }
}
