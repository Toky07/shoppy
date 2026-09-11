<?php

declare(strict_types=1);

use App\User\Application\Query\GetUserQuery;
use App\User\Application\QueryHandler\GetUserQueryHandler;
use App\User\Domain\Entity\User;
use App\User\Domain\Exception\UserNotFound;
use App\User\Domain\ValueObject\Email;
use App\User\Domain\ValueObject\Role;
use App\User\Domain\ValueObject\UserId;
use App\User\Infrastructure\Persistence\InMemoryUserRepository;

it('returns a user identity', function () {
    $repository = new InMemoryUserRepository();
    $id = UserId::fromString('11111111-1111-4111-8111-111111111111');
    $repository->save(User::register(
        $id,
        Email::fromString('ada@nuvora.test'),
        new DateTimeImmutable('2026-08-20T12:00:00+00:00'),
    ));

    $response = (new GetUserQueryHandler($repository))->handle(new GetUserQuery($id->value()));

    expect($response->toArray())->toBe([
        'id' => $id->value(),
        'email' => 'ada@nuvora.test',
        'createdAt' => '2026-08-20T12:00:00+00:00',
        'role' => 'customer',
    ]);
});

it('exposes an assigned admin role', function () {
    $repository = new InMemoryUserRepository();
    $id = UserId::fromString('11111111-1111-4111-8111-111111111111');
    $user = User::register(
        $id,
        Email::fromString('ada@nuvora.test'),
        new DateTimeImmutable('2026-08-20T12:00:00+00:00'),
    );
    $user->assignRole(Role::admin());
    $repository->save($user);

    $response = (new GetUserQueryHandler($repository))->handle(new GetUserQuery($id->value()));

    expect($response->toArray()['role'])->toBe('admin');
});

it('fails when the user does not exist', function () {
    $handler = new GetUserQueryHandler(new InMemoryUserRepository());

    $handler->handle(new GetUserQuery('11111111-1111-4111-8111-111111111111'));
})->throws(UserNotFound::class);
