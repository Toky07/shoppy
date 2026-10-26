<?php

declare(strict_types=1);

namespace App\Product\Domain\Exception;

use App\Shared\Domain\Exception\InvalidValue;

final class InvalidReviewPagination extends \InvalidArgumentException implements InvalidValue
{
    public function __construct()
    {
        parent::__construct('Review list page and limit must be valid.');
    }

    public function errorCode(): string
    {
        return 'invalid_review_pagination';
    }

    public function field(): string
    {
        return 'page';
    }
}
