<?php

declare(strict_types=1);

namespace App\Audit\Domain\Exception;

use App\Shared\Domain\Exception\InvalidValue;

final class InvalidAuditId extends \InvalidArgumentException implements InvalidValue
{
    public function __construct()
    {
        parent::__construct('Audit id must be a UUID.');
    }

    public function errorCode(): string
    {
        return 'invalid_audit_id';
    }

    public function field(): string
    {
        return 'id';
    }
}
