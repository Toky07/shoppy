<?php

declare(strict_types=1);

namespace App\Media\Domain\Exception;

use App\Shared\Domain\Exception\InvalidValue;

final class InvalidMediaRelativePath extends \InvalidArgumentException implements InvalidValue
{
    public function __construct()
    {
        parent::__construct('Media path must follow the uploads/YYYY/MM/filename layout.');
    }

    public function errorCode(): string
    {
        return 'invalid_media_path';
    }

    public function field(): string
    {
        return 'path';
    }
}
