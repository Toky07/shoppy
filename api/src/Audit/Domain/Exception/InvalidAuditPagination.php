<?php

declare(strict_types=1);

namespace App\Audit\Domain\Exception;

use App\Shared\Domain\Exception\InvalidValue;

final class InvalidAuditPagination extends \InvalidArgumentException implements InvalidValue
{
    public function __construct()
    {
        parent::__construct('Page must be at least 1 and limit must be between 1 and 100.');
    }

    public function errorCode(): string
    {
        return 'invalid_audit_pagination';
    }

    public function field(): string
    {
        return 'page';
    }
}
