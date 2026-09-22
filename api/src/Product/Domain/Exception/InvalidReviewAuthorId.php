<?php

declare(strict_types=1);

namespace App\Product\Domain\Exception;

use App\Shared\Domain\Exception\InvalidValue;

final class InvalidReviewAuthorId extends \InvalidArgumentException implements InvalidValue
{
    public function __construct()
    {
        parent::__construct('Review author id must be a valid UUID.');
    }

    public function errorCode(): string
    {
        return 'invalid_review_author_id';
    }

    public function field(): string
    {
        return 'authorId';
    }
}
