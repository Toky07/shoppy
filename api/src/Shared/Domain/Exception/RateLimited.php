<?php

declare(strict_types=1);

namespace App\Shared\Domain\Exception;

final class RateLimited extends \RuntimeException implements DomainException
{
    public function __construct()
    {
        parent::__construct('Too many requests.');
    }

    public function errorCode(): string
    {
        return 'rate_limited';
    }
}
