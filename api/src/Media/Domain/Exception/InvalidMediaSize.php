<?php

declare(strict_types=1);

namespace App\Media\Domain\Exception;

use App\Shared\Domain\Exception\InvalidValue;

final class InvalidMediaSize extends \InvalidArgumentException implements InvalidValue
{
    public function __construct()
    {
        parent::__construct('Media size must be between 1 byte and 10 MB.');
    }

    public function errorCode(): string
    {
        return 'invalid_media_size';
    }

    public function field(): string
    {
        return 'file';
    }
}
