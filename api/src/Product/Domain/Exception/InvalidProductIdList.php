<?php

declare(strict_types=1);

namespace App\Product\Domain\Exception;

use App\Shared\Domain\Exception\InvalidValue;

final class InvalidProductIdList extends \InvalidArgumentException implements InvalidValue
{
    public function __construct(string $message)
    {
        parent::__construct($message);
    }

    public function errorCode(): string
    {
        return 'invalid_product_ids';
    }

    public function field(): string
    {
        return 'ids';
    }
}
