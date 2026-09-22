<?php

declare(strict_types=1);

namespace App\Order\Domain\Exception;

use App\Shared\Domain\Exception\InvalidValue;

final class InvalidPostalAddress extends \InvalidArgumentException implements InvalidValue
{
    public function __construct(
        private string $fieldName,
        string $message,
    ) {
        parent::__construct($message);
    }

    public function errorCode(): string
    {
        return 'invalid_postal_address';
    }

    public function field(): string
    {
        return $this->fieldName;
    }
}
