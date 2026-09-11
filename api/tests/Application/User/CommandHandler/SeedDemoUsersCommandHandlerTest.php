<?php

declare(strict_types=1);

use App\User\Application\Command\SeedDemoUsersCommand;
use App\User\Application\CommandHandler\SeedDemoUsersCommandHandler;
use App\User\Application\DemoAccounts;
use App\User\Domain\ValueObject\Email;
use App\User\Domain\ValueObject\Role;
use App\User\Infrastructure\Persistence\InMemoryUserRepository;
use App\Auth\Infrastructure\Persistence\InMemoryCredentialsRepository;
use App\Tests\Doubles\FakePasswordHasher;
use App\Tests\Doubles\FixedClock;

it('creates admin and visitor accounts', function () {
    $users = new InMemoryUserRepository();
    $credentials = new InMemoryCredentialsRepository();
    $handler = new SeedDemoUsersCommandHandler(
        $users,
        $credentials,
        new FakePasswordHasher(),
        new FixedClock(new DateTimeImmutable('2026-08-20T12:00:00+00:00')),
    );

    $created = $handler->handle(new SeedDemoUsersCommand());

    $admin = $users->findByEmail(Email::fromString(DemoAccounts::ADMIN_EMAIL));
    $visitor = $users->findByEmail(Email::fromString(DemoAccounts::VISITOR_EMAIL));

    expect($created)->toBe(2)
        ->and($admin?->role())->toEqual(Role::admin())
        ->and($visitor?->role())->toEqual(Role::customer())
        ->and($credentials->findByUserId($admin->id()))->not->toBeNull()
        ->and($credentials->findByUserId($visitor->id()))->not->toBeNull();
});

it('is idempotent', function () {
    $users = new InMemoryUserRepository();
    $handler = new SeedDemoUsersCommandHandler(
        $users,
        new InMemoryCredentialsRepository(),
        new FakePasswordHasher(),
        new FixedClock(new DateTimeImmutable('2026-08-20T12:00:00+00:00')),
    );

    $handler->handle(new SeedDemoUsersCommand());
    $again = $handler->handle(new SeedDemoUsersCommand());

    expect($again)->toBe(0);
});
