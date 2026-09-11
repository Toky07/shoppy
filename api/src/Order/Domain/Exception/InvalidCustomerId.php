<?php

declare(strict_types=1);

namespace App\Order\Domain\Exception;

use App\Shared\Domain\Exception\InvalidValue;

final class InvalidCustomerId extends \InvalidArgumentException implements InvalidValue
{
    public function __construct()
    {
        parent::__construct('Customer id must be a valid UUID.');
    }

    public function errorCode(): string
    {
        return 'invalid_customer_id';
    }

    public function field(): string
    {
        return 'customerId';
    }
}
