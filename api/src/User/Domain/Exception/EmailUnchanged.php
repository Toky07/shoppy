<?php

declare(strict_types=1);

namespace App\User\Domain\Exception;

use App\Shared\Domain\Exception\InvalidValue;

final class EmailUnchanged extends \InvalidArgumentException implements InvalidValue
{
    public function __construct()
    {
        parent::__construct('Email is unchanged.');
    }

    public function errorCode(): string
    {
        return 'email_unchanged';
    }

    public function field(): string
    {
        return 'email';
    }
}
