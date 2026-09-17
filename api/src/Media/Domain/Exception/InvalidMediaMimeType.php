<?php

declare(strict_types=1);

namespace App\Media\Domain\Exception;

use App\Shared\Domain\Exception\InvalidValue;

final class InvalidMediaMimeType extends \InvalidArgumentException implements InvalidValue
{
    public function __construct()
    {
        parent::__construct('Media mime type is not supported.');
    }

    public function errorCode(): string
    {
        return 'invalid_media_mime_type';
    }

    public function field(): string
    {
        return 'mimeType';
    }
}
