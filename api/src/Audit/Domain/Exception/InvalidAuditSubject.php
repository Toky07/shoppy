<?php

declare(strict_types=1);

namespace App\Audit\Domain\Exception;

use App\Shared\Domain\Exception\InvalidValue;

final class InvalidAuditSubject extends \InvalidArgumentException implements InvalidValue
{
    public function __construct()
    {
        parent::__construct('Audit subject must be a user, product, or order.');
    }

    public function errorCode(): string
    {
        return 'invalid_audit_subject';
    }

    public function field(): string
    {
        return 'subjectType';
    }
}
