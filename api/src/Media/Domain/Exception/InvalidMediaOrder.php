<?php

declare(strict_types=1);

namespace App\Media\Domain\Exception;

use App\Shared\Domain\Exception\InvalidValue;

final class InvalidMediaOrder extends \InvalidArgumentException implements InvalidValue
{
    public function __construct()
    {
        parent::__construct('Media order must list every image of this owner once.');
    }

    public function errorCode(): string
    {
        return 'invalid_media_order';
    }

    public function field(): string
    {
        return 'ids';
    }
}
