<?php

declare(strict_types=1);

namespace App\Invoice\Domain\Exception;

use App\Shared\Domain\Exception\InvalidValue;

final class InvalidCustomerReference extends \InvalidArgumentException implements InvalidValue
{
    public function __construct()
    {
        parent::__construct('Customer id must be a valid UUID.');
    }

    public function errorCode(): string
    {
        return 'invalid_invoice_customer_id';
    }

    public function field(): string
    {
        return 'customerId';
    }
}
