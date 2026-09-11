<?php

declare(strict_types=1);

use App\User\Domain\Entity\User;
use App\User\Domain\ValueObject\Email;
use App\User\Domain\ValueObject\Role;
use App\User\Domain\ValueObject\UserId;

it('registers with the customer role by default', function () {
    $id = UserId::fromString('11111111-1111-4111-8111-111111111111');
    $email = Email::fromString('ada@nuvora.test');
    $createdAt = new DateTimeImmutable('2026-08-20T12:00:00+00:00');

    $user = User::register($id, $email, $createdAt);

    expect($user->id())->toBe($id)
        ->and($user->email())->toBe($email)
        ->and($user->createdAt())->toBe($createdAt)
        ->and($user->role())->toEqual(Role::customer());
});

it('can be assigned the admin role', function () {
    $user = User::register(
        UserId::fromString('11111111-1111-4111-8111-111111111111'),
        Email::fromString('ada@nuvora.test'),
        new DateTimeImmutable('2026-08-20T12:00:00+00:00'),
    );

    $user->assignRole(Role::admin());

    expect($user->role())->toEqual(Role::admin());
});

it('can be reconstituted with a stored role', function () {
    $user = User::register(
        UserId::fromString('11111111-1111-4111-8111-111111111111'),
        Email::fromString('ada@nuvora.test'),
        new DateTimeImmutable('2026-08-20T12:00:00+00:00'),
        Role::admin(),
    );

    expect($user->role())->toEqual(Role::admin());
});
