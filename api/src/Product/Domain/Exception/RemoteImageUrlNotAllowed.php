<?php

declare(strict_types=1);

namespace App\Product\Domain\Exception;

use App\Shared\Domain\Exception\InvalidValue;

final class RemoteImageUrlNotAllowed extends \InvalidArgumentException implements InvalidValue
{
    public function __construct()
    {
        parent::__construct('Remote image URL is not allowed.');
    }

    public function errorCode(): string
    {
        return 'remote_image_url_not_allowed';
    }

    public function field(): string
    {
        return 'images';
    }
}
