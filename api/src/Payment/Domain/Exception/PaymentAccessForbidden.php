<?php

declare(strict_types=1);

namespace App\Payment\Domain\Exception;

use App\Shared\Domain\Exception\ForbiddenException;

final class PaymentAccessForbidden extends \RuntimeException implements ForbiddenException
{
    public function __construct()
    {
        parent::__construct('The request is not authorized.');
    }

    public function errorCode(): string
    {
        return 'forbidden';
    }
}
