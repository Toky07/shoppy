<?php

declare(strict_types=1);

use App\Invoice\Application\Port\InvoiceAdminContact;
use App\Invoice\Infrastructure\Customer\UserInvoiceAdminContact;
use App\Invoice\Infrastructure\Notification\EmailAdminOnInvoiceIssued;
use App\Invoice\Infrastructure\Notification\EmailCustomerOnInvoiceIssued;
use App\Invoice\Infrastructure\Notification\InvoiceIssuedMail;

it('wires invoice issued emails', function () {
    expect(self::getContainer()->get(InvoiceIssuedMail::class))->toBeInstanceOf(InvoiceIssuedMail::class)
        ->and(self::getContainer()->get(InvoiceAdminContact::class))->toBeInstanceOf(UserInvoiceAdminContact::class)
        ->and(self::getContainer()->get(EmailCustomerOnInvoiceIssued::class))->toBeInstanceOf(EmailCustomerOnInvoiceIssued::class)
        ->and(self::getContainer()->get(EmailAdminOnInvoiceIssued::class))->toBeInstanceOf(EmailAdminOnInvoiceIssued::class);
});
