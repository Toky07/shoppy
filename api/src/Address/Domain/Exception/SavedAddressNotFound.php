<?php

declare(strict_types=1);

namespace App\Address\Domain\Exception;

use App\Address\Domain\ValueObject\SavedAddressId;
use App\Shared\Domain\Exception\NotFoundException;

final class SavedAddressNotFound extends \RuntimeException implements NotFoundException
{
    public function __construct(SavedAddressId $id)
    {
        parent::__construct(sprintf('Saved address "%s" was not found.', $id->value()));
    }

    public function errorCode(): string
    {
        return 'saved_address_not_found';
    }
}
