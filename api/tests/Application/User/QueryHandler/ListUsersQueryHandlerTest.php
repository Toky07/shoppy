<?php

declare(strict_types=1);

use App\User\Application\Query\ListUsersQuery;
use App\User\Application\QueryHandler\ListUsersQueryHandler;
use App\User\Domain\Entity\User;
use App\User\Domain\Exception\InvalidUserPagination;
use App\User\Domain\ValueObject\Email;
use App\User\Domain\ValueObject\Role;
use App\User\Domain\ValueObject\UserId;
use App\User\Infrastructure\Persistence\InMemoryUserRepository;

function saveListedUser(
    InMemoryUserRepository $repository,
    string $id,
    string $email,
    DateTimeImmutable $createdAt,
    ?Role $role = null,
): void {
    $repository->save(User::register(
        UserId::fromString($id),
        Email::fromString($email),
        $createdAt,
        $role,
    ));
}

it('lists users newest first', function () {
    $repository = new InMemoryUserRepository();
    saveListedUser($repository, '11111111-1111-4111-8111-111111111111', 'old@nuvora.test', new DateTimeImmutable('2026-08-19T12:00:00+00:00'));
    saveListedUser($repository, '22222222-2222-4222-8222-222222222222', 'new@nuvora.test', new DateTimeImmutable('2026-08-21T12:00:00+00:00'), Role::admin());
    saveListedUser($repository, '33333333-3333-4333-8333-333333333333', 'mid@nuvora.test', new DateTimeImmutable('2026-08-20T12:00:00+00:00'));

    $response = (new ListUsersQueryHandler($repository))->handle(new ListUsersQuery());

    expect($response->toArray())->toMatchArray([
        'page' => 1,
        'limit' => 20,
        'total' => 3,
    ])
        ->and($response->toArray()['items'][0]['email'])->toBe('new@nuvora.test')
        ->and($response->toArray()['items'][0]['role'])->toBe('admin')
        ->and($response->toArray()['items'][1]['email'])->toBe('mid@nuvora.test')
        ->and($response->toArray()['items'][2]['email'])->toBe('old@nuvora.test');
});

it('paginates users', function () {
    $repository = new InMemoryUserRepository();
    saveListedUser($repository, '11111111-1111-4111-8111-111111111111', 'one@nuvora.test', new DateTimeImmutable('2026-08-18T12:00:00+00:00'));
    saveListedUser($repository, '22222222-2222-4222-8222-222222222222', 'two@nuvora.test', new DateTimeImmutable('2026-08-19T12:00:00+00:00'));
    saveListedUser($repository, '33333333-3333-4333-8333-333333333333', 'three@nuvora.test', new DateTimeImmutable('2026-08-20T12:00:00+00:00'));

    $response = (new ListUsersQueryHandler($repository))->handle(new ListUsersQuery(
        page: 2,
        limit: 2,
    ));

    expect($response->total)->toBe(3)
        ->and($response->items)->toHaveCount(1)
        ->and($response->items[0]->email)->toBe('one@nuvora.test');
});

it('filters users by email', function () {
    $repository = new InMemoryUserRepository();
    saveListedUser($repository, '11111111-1111-4111-8111-111111111111', 'ada@nuvora.test', new DateTimeImmutable('2026-08-19T12:00:00+00:00'));
    saveListedUser($repository, '22222222-2222-4222-8222-222222222222', 'bob@shoppy.test', new DateTimeImmutable('2026-08-20T12:00:00+00:00'));

    $response = (new ListUsersQueryHandler($repository))->handle(new ListUsersQuery(
        search: 'NUVORA',
    ));

    expect($response->total)->toBe(1)
        ->and($response->items[0]->email)->toBe('ada@nuvora.test');
});

it('rejects an invalid page', function () {
    (new ListUsersQueryHandler(new InMemoryUserRepository()))->handle(new ListUsersQuery(
        page: 0,
    ));
})->throws(InvalidUserPagination::class);
