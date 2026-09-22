<?php

declare(strict_types=1);

namespace App\Product\Domain\Exception;

use App\Shared\Domain\Exception\InvalidValue;

final class InvalidReviewBody extends \InvalidArgumentException implements InvalidValue
{
    public function __construct()
    {
        parent::__construct('Review must be between 1 and 1000 characters.');
    }

    public function errorCode(): string
    {
        return 'invalid_review_body';
    }

    public function field(): string
    {
        return 'body';
    }
}
