<?php

declare(strict_types=1);

namespace App\Order\Domain\Exception;

use App\Shared\Domain\Exception\InvalidValue;

final class InvalidOrderPagination extends \InvalidArgumentException implements InvalidValue
{
    public function __construct()
    {
        parent::__construct('Page must be at least 1 and limit must be between 1 and 100.');
    }

    public function errorCode(): string
    {
        return 'invalid_order_pagination';
    }

    public function field(): string
    {
        return 'page';
    }
}
