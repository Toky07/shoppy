<?php

declare(strict_types=1);

namespace App\Payment\Domain\Exception;

use App\Payment\Domain\ValueObject\OrderReference;
use App\Shared\Domain\Exception\NotFoundException;

final class PaymentNotFound extends \RuntimeException implements NotFoundException
{
    public function __construct(OrderReference $orderId)
    {
        parent::__construct(sprintf('Payment for order "%s" was not found.', $orderId->value()));
    }

    public function errorCode(): string
    {
        return 'payment_not_found';
    }
}
