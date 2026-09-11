<?php

declare(strict_types=1);

use App\User\Application\Command\AssignUserRoleCommand;
use App\User\Application\CommandHandler\AssignUserRoleCommandHandler;
use App\User\Domain\Entity\User;
use App\User\Domain\Exception\InvalidUserId;
use App\User\Domain\Exception\InvalidUserRole;
use App\User\Domain\Exception\UserNotFound;
use App\User\Domain\ValueObject\Email;
use App\User\Domain\ValueObject\Role;
use App\User\Domain\ValueObject\UserId;
use App\User\Infrastructure\Persistence\InMemoryUserRepository;

function persistedUser(InMemoryUserRepository $users): User
{
    $user = User::register(
        UserId::fromString('11111111-1111-4111-8111-111111111111'),
        Email::fromString('ada@nuvora.test'),
        new DateTimeImmutable('2026-08-20T12:00:00+00:00'),
    );
    $users->save($user);

    return $user;
}

it('assigns the admin role', function () {
    $users = new InMemoryUserRepository();
    $user = persistedUser($users);

    (new AssignUserRoleCommandHandler($users))->handle(new AssignUserRoleCommand(
        id: $user->id()->value(),
        role: 'admin',
    ));

    expect($users->findById($user->id())->role())->toEqual(Role::admin());
});

it('assigns the customer role', function () {
    $users = new InMemoryUserRepository();
    $user = persistedUser($users);
    $user->assignRole(Role::admin());
    $users->save($user);

    (new AssignUserRoleCommandHandler($users))->handle(new AssignUserRoleCommand(
        id: $user->id()->value(),
        role: 'customer',
    ));

    expect($users->findById($user->id())->role())->toEqual(Role::customer());
});

it('fails when the user does not exist', function () {
    (new AssignUserRoleCommandHandler(new InMemoryUserRepository()))->handle(new AssignUserRoleCommand(
        id: '11111111-1111-4111-8111-111111111111',
        role: 'admin',
    ));
})->throws(UserNotFound::class);

it('rejects an unknown role', function () {
    $users = new InMemoryUserRepository();
    $user = persistedUser($users);

    (new AssignUserRoleCommandHandler($users))->handle(new AssignUserRoleCommand(
        id: $user->id()->value(),
        role: 'superuser',
    ));
})->throws(InvalidUserRole::class);

it('rejects an invalid user id', function () {
    (new AssignUserRoleCommandHandler(new InMemoryUserRepository()))->handle(new AssignUserRoleCommand(
        id: 'not-a-uuid',
        role: 'admin',
    ));
})->throws(InvalidUserId::class);
