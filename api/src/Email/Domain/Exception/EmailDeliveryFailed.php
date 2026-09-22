<?php

declare(strict_types=1);

namespace App\Email\Domain\Exception;

use App\Shared\Domain\Exception\DomainException;

final class EmailDeliveryFailed extends \RuntimeException implements DomainException
{
    public function __construct(?\Throwable $previous = null)
    {
        parent::__construct('The email could not be delivered.', 0, $previous);
    }

    public function errorCode(): string
    {
        return 'email_delivery_failed';
    }
}
