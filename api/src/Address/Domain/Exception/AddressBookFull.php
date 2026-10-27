<?php

declare(strict_types=1);

namespace App\Address\Domain\Exception;

use App\Shared\Domain\Exception\ConflictException;

final class AddressBookFull extends \RuntimeException implements ConflictException
{
    public function __construct()
    {
        parent::__construct('The address book cannot hold more than 10 addresses.');
    }

    public function errorCode(): string
    {
        return 'address_book_full';
    }
}
