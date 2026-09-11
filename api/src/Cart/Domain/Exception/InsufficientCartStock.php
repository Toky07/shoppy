<?php

declare(strict_types=1);

namespace App\Cart\Domain\Exception;

use App\Shared\Domain\Exception\ConflictException;

final class InsufficientCartStock extends \RuntimeException implements ConflictException
{
    public function __construct(int $available, int $requested)
    {
        parent::__construct(sprintf(
            'Insufficient product stock: available %d, requested %d.',
            $available,
            $requested,
        ));
    }

    public function errorCode(): string
    {
        return 'insufficient_product_stock';
    }
}
