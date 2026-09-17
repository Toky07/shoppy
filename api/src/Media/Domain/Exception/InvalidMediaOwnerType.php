<?php

declare(strict_types=1);

namespace App\Media\Domain\Exception;

use App\Shared\Domain\Exception\InvalidValue;

final class InvalidMediaOwnerType extends \InvalidArgumentException implements InvalidValue
{
    public function __construct()
    {
        parent::__construct('Media owner type must be a known entity type.');
    }

    public function errorCode(): string
    {
        return 'invalid_media_owner_type';
    }

    public function field(): string
    {
        return 'ownerType';
    }
}
