<?php

declare(strict_types=1);

namespace App\Media\Domain\Exception;

use App\Media\Domain\ValueObject\MediaId;
use App\Shared\Domain\Exception\NotFoundException;

final class MediaNotFound extends \RuntimeException implements NotFoundException
{
    public function __construct(MediaId $id)
    {
        parent::__construct(sprintf('Media "%s" was not found.', $id->value()));
    }

    public function errorCode(): string
    {
        return 'media_not_found';
    }
}
