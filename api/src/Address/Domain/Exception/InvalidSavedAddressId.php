<?php

declare(strict_types=1);

namespace App\Address\Domain\Exception;

use App\Shared\Domain\Exception\InvalidValue;

final class InvalidSavedAddressId extends \InvalidArgumentException implements InvalidValue
{
    public function __construct()
    {
        parent::__construct('Address id must be a valid UUID.');
    }

    public function errorCode(): string
    {
        return 'invalid_saved_address_id';
    }

    public function field(): string
    {
        return 'addressId';
    }
}
