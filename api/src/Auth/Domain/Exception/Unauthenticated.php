<?php

declare(strict_types=1);

namespace App\Auth\Domain\Exception;

use App\Shared\Domain\Exception\UnauthenticatedException;

final class Unauthenticated extends \RuntimeException implements UnauthenticatedException
{
    public function __construct()
    {
        parent::__construct('The request is not authenticated.');
    }

    public function errorCode(): string
    {
        return 'unauthenticated';
    }
}
