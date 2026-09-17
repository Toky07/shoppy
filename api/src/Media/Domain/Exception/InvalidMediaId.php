<?php

declare(strict_types=1);

namespace App\Media\Domain\Exception;

use App\Shared\Domain\Exception\InvalidValue;

final class InvalidMediaId extends \InvalidArgumentException implements InvalidValue
{
    public function __construct()
    {
        parent::__construct('Media id must be a valid UUID.');
    }

    public function errorCode(): string
    {
        return 'invalid_media_id';
    }

    public function field(): string
    {
        return 'id';
    }
}
