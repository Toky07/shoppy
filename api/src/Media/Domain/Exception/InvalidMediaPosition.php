<?php

declare(strict_types=1);

namespace App\Media\Domain\Exception;

use App\Shared\Domain\Exception\InvalidValue;

final class InvalidMediaPosition extends \InvalidArgumentException implements InvalidValue
{
    public function __construct()
    {
        parent::__construct('Media position cannot be negative.');
    }

    public function errorCode(): string
    {
        return 'invalid_media_position';
    }

    public function field(): string
    {
        return 'position';
    }
}
