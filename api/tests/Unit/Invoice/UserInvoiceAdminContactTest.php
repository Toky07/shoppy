<?php

declare(strict_types=1);

use App\Invoice\Infrastructure\Customer\UserInvoiceAdminContact;
use App\User\Domain\Entity\User;
use App\User\Domain\ValueObject\Email;
use App\User\Domain\ValueObject\Role;
use App\User\Domain\ValueObject\UserId;
use App\User\Infrastructure\Persistence\InMemoryUserRepository;

it('lists emails of active administrators', function () {
    $users = new InMemoryUserRepository();
    $users->save(User::register(
        UserId::fromString('11111111-1111-4111-8111-111111111111'),
        Email::fromString('ada@shoppy.test'),
        new DateTimeImmutable('2026-08-20T12:00:00+00:00'),
    ));
    $admin = User::register(
        UserId::fromString('22222222-2222-4222-8222-222222222222'),
        Email::fromString('admin@shoppy.test'),
        new DateTimeImmutable('2026-08-20T12:00:00+00:00'),
        Role::admin(),
    );
    $users->save($admin);
    $deleted = User::register(
        UserId::fromString('33333333-3333-4333-8333-333333333333'),
        Email::fromString('old-admin@shoppy.test'),
        new DateTimeImmutable('2026-08-19T12:00:00+00:00'),
        Role::admin(),
    );
    $deleted->anonymize(new DateTimeImmutable('2026-08-21T12:00:00+00:00'));
    $users->save($deleted);

    expect((new UserInvoiceAdminContact($users))->emails())->toBe(['admin@shoppy.test']);
});

it('returns no administrators when none exist', function () {
    expect((new UserInvoiceAdminContact(new InMemoryUserRepository()))->emails())->toBe([]);
});
