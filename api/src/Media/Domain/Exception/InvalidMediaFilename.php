<?php

declare(strict_types=1);

namespace App\Media\Domain\Exception;

use App\Shared\Domain\Exception\InvalidValue;

final class InvalidMediaFilename extends \InvalidArgumentException implements InvalidValue
{
    public function __construct()
    {
        parent::__construct('Media filename is invalid.');
    }

    public function errorCode(): string
    {
        return 'invalid_media_filename';
    }

    public function field(): string
    {
        return 'filename';
    }
}
