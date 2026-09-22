<?php

declare(strict_types=1);

namespace App\Payment\Infrastructure\Customer;

use App\Payment\Application\Port\PayerContact;
use App\Payment\Domain\ValueObject\CustomerReference;
use App\User\Domain\Repository\UserRepository;
use App\User\Domain\ValueObject\UserId;

final readonly class UserPayerContact implements PayerContact
{
    public function __construct(private UserRepository $users)
    {
    }

    public function emailFor(CustomerReference $customerId): ?string
    {
        return $this->users->findById(UserId::fromString($customerId->value()))?->email()->value();
    }
}
