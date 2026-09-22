<?php

declare(strict_types=1);

namespace App\Product\Domain\Exception;

use App\Shared\Domain\Exception\InvalidValue;

final class InvalidRating extends \InvalidArgumentException implements InvalidValue
{
    public function __construct()
    {
        parent::__construct('Rating must be an integer between 1 and 5.');
    }

    public function errorCode(): string
    {
        return 'invalid_rating';
    }

    public function field(): string
    {
        return 'rating';
    }
}
