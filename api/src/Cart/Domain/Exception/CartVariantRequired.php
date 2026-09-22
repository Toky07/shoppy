<?php

declare(strict_types=1);

namespace App\Cart\Domain\Exception;

use App\Shared\Domain\Exception\InvalidValue;

final class CartVariantRequired extends \InvalidArgumentException implements InvalidValue
{
    public function __construct()
    {
        parent::__construct('Choose a size or a color.');
    }

    public function errorCode(): string
    {
        return 'variant_required';
    }

    public function field(): string
    {
        return 'variantId';
    }
}
