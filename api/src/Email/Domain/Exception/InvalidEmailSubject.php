<?php

declare(strict_types=1);

namespace App\Email\Domain\Exception;

use App\Shared\Domain\Exception\InvalidValue;

final class InvalidEmailSubject extends \InvalidArgumentException implements InvalidValue
{
    public function __construct()
    {
        parent::__construct('Email subject is invalid.');
    }

    public function errorCode(): string
    {
        return 'invalid_email_subject';
    }

    public function field(): string
    {
        return 'subject';
    }
}
