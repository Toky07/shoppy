<?php

declare(strict_types=1);

namespace App\Email\Domain\Exception;

use App\Shared\Domain\Exception\InvalidValue;

final class InvalidEmailAddress extends \InvalidArgumentException implements InvalidValue
{
    public function __construct()
    {
        parent::__construct('Email must be a valid email address.');
    }

    public function errorCode(): string
    {
        return 'invalid_email_address';
    }

    public function field(): string
    {
        return 'to';
    }
}
