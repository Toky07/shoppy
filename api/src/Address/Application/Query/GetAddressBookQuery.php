<?php

declare(strict_types=1);

namespace App\Address\Application\Query;

final readonly class GetAddressBookQuery
{
    public function __construct(public string $customerId)
    {
    }
}
