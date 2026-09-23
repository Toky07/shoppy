<?php

declare(strict_types=1);

namespace App\Payment\Domain\Exception;

use App\Shared\Domain\Exception\ForbiddenException;

final class LocalPaymentDisabled extends \RuntimeException implements ForbiddenException
{
    public function __construct()
    {
        parent::__construct('Local payment completion is not available.');
    }

    public function errorCode(): string
    {
        return 'local_payment_disabled';
    }
}
