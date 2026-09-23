<?php

declare(strict_types=1);

namespace App\Payment\Domain\Exception;

use App\Shared\Domain\Exception\InvalidValue;

final class CheckoutUrlNotAllowed extends \InvalidArgumentException implements InvalidValue
{
    public function __construct(private string $fieldName)
    {
        parent::__construct('This URL is not allowed.');
    }

    public function errorCode(): string
    {
        return 'checkout_url_not_allowed';
    }

    public function field(): string
    {
        return $this->fieldName;
    }
}
