<?php

declare(strict_types=1);

namespace App\Audit\Domain\Exception;

use App\Shared\Domain\Exception\InvalidValue;

final class InvalidAuditAction extends \InvalidArgumentException implements InvalidValue
{
    public function __construct()
    {
        parent::__construct('Audit action must be role_changed, price_changed, stock_changed, or payment_changed.');
    }

    public function errorCode(): string
    {
        return 'invalid_audit_action';
    }

    public function field(): string
    {
        return 'action';
    }
}
