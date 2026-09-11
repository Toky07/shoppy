<?php

declare(strict_types=1);

namespace App\User\Domain\Exception;

use App\Shared\Domain\Exception\ConflictException;
use App\User\Domain\ValueObject\Email;

final class EmailAlreadyRegistered extends \RuntimeException implements ConflictException
{
    public function __construct(Email $email)
    {
        parent::__construct(sprintf('Email "%s" is already registered.', $email->value()));
    }

    public function errorCode(): string
    {
        return 'email_already_registered';
    }
}
