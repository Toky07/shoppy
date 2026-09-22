<?php

declare(strict_types=1);

namespace App\Product\Domain\Exception;

use App\Shared\Domain\Exception\NotFoundException;

final class CategoryNotFound extends \RuntimeException implements NotFoundException
{
    public function __construct(string $identifier)
    {
        parent::__construct(sprintf('Category "%s" was not found.', $identifier));
    }

    public function errorCode(): string
    {
        return 'category_not_found';
    }
}
