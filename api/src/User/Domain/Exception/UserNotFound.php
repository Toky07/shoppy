<?php

declare(strict_types=1);

namespace App\User\Domain\Exception;

use App\Shared\Domain\Exception\NotFoundException;
use App\User\Domain\ValueObject\UserId;

final class UserNotFound extends \RuntimeException implements NotFoundException
{
    public function __construct(UserId $id)
    {
        parent::__construct(sprintf('User "%s" was not found.', $id->value()));
    }

    public function errorCode(): string
    {
        return 'user_not_found';
    }
}
