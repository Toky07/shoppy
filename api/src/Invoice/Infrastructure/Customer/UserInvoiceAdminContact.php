<?php

declare(strict_types=1);

namespace App\Invoice\Infrastructure\Customer;

use App\Invoice\Application\Port\InvoiceAdminContact;
use App\User\Domain\Repository\UserRepository;
use App\User\Domain\ValueObject\Role;

final readonly class UserInvoiceAdminContact implements InvoiceAdminContact
{
    public function __construct(private UserRepository $users)
    {
    }

    public function emails(): array
    {
        $emails = [];

        foreach ($this->users->findWithRole(Role::admin()) as $user) {
            $emails[] = $user->email()->value();
        }

        return $emails;
    }
}
