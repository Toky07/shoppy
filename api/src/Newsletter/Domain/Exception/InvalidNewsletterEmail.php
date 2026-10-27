<?php

declare(strict_types=1);

namespace App\Newsletter\Domain\Exception;

use App\Shared\Domain\Exception\InvalidValue;

final class InvalidNewsletterEmail extends \InvalidArgumentException implements InvalidValue
{
    public function __construct()
    {
        parent::__construct('Email must be a valid email address.');
    }

    public function errorCode(): string
    {
        return 'invalid_newsletter_email';
    }

    public function field(): string
    {
        return 'email';
    }
}
