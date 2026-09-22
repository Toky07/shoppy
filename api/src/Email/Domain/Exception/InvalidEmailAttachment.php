<?php

declare(strict_types=1);

namespace App\Email\Domain\Exception;

use App\Shared\Domain\Exception\InvalidValue;

final class InvalidEmailAttachment extends \InvalidArgumentException implements InvalidValue
{
    public function __construct()
    {
        parent::__construct('Email attachment is invalid.');
    }

    public function errorCode(): string
    {
        return 'invalid_email_attachment';
    }

    public function field(): string
    {
        return 'attachment';
    }
}
