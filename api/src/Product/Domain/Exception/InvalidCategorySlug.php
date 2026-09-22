<?php

declare(strict_types=1);

namespace App\Product\Domain\Exception;

use App\Shared\Domain\Exception\InvalidValue;

final class InvalidCategorySlug extends \InvalidArgumentException implements InvalidValue
{
    public function __construct()
    {
        parent::__construct('Category slug must be valid.');
    }

    public function errorCode(): string
    {
        return 'invalid_category_slug';
    }

    public function field(): string
    {
        return 'category';
    }
}
