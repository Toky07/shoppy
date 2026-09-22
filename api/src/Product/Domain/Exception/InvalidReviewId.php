<?php

declare(strict_types=1);

namespace App\Product\Domain\Exception;

use App\Shared\Domain\Exception\InvalidValue;

final class InvalidReviewId extends \InvalidArgumentException implements InvalidValue
{
    public function __construct()
    {
        parent::__construct('Review id must be a valid UUID.');
    }

    public function errorCode(): string
    {
        return 'invalid_review_id';
    }

    public function field(): string
    {
        return 'id';
    }
}
