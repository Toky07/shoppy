<?php

declare(strict_types=1);

namespace App\Order\Infrastructure\Customer;

use App\Order\Application\Port\CustomerContact;
use App\Order\Domain\ValueObject\CustomerId;
use App\User\Domain\Repository\UserRepository;
use App\User\Domain\ValueObject\UserId;

final readonly class UserCustomerContact implements CustomerContact
{
    public function __construct(private UserRepository $users)
    {
    }

    public function emailFor(CustomerId $customerId): ?string
    {
        return $this->users->findById(UserId::fromString($customerId->value()))?->email()->value();
    }
}
