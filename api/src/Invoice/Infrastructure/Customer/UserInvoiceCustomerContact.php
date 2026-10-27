<?php

declare(strict_types=1);

namespace App\Invoice\Infrastructure\Customer;

use App\Invoice\Application\Port\InvoiceCustomerContact;
use App\Invoice\Domain\ValueObject\CustomerReference;
use App\User\Domain\Repository\UserRepository;
use App\User\Domain\ValueObject\UserId;

final readonly class UserInvoiceCustomerContact implements InvoiceCustomerContact
{
    public function __construct(private UserRepository $users)
    {
    }

    public function emailFor(CustomerReference $customerId): ?string
    {
        return $this->users->findById(UserId::fromString($customerId->value()))?->email()->value();
    }
}
