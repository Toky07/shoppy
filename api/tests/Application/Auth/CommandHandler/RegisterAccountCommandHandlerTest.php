<?php

declare(strict_types=1);

use App\Auth\Application\Command\RegisterAccountCommand;
use App\Auth\Application\CommandHandler\RegisterAccountCommandHandler;
use App\Auth\Domain\Exception\InvalidPassword;
use App\Auth\Infrastructure\Persistence\InMemoryCredentialsRepository;
use App\Tests\Doubles\FakePasswordHasher;
use App\Tests\Doubles\FixedClock;
use App\User\Application\CommandHandler\RegisterUserCommandHandler;
use App\User\Domain\Exception\EmailAlreadyRegistered;
use App\User\Domain\ValueObject\Email;
use App\User\Domain\ValueObject\Role;
use App\User\Infrastructure\Persistence\InMemoryUserRepository;

it('registers a user and stores hashed credentials', function () {
    $users = new InMemoryUserRepository();
    $credentials = new InMemoryCredentialsRepository();
    $createdAt = new DateTimeImmutable('2026-08-20T12:00:00+00:00');
    $handler = new RegisterAccountCommandHandler(
        new RegisterUserCommandHandler($users, new FixedClock($createdAt)),
        $credentials,
        new FakePasswordHasher(),
    );

    $userId = $handler->handle(new RegisterAccountCommand(
        email: 'Ada@Nuvora.test',
        password: 'secret-secret',
    ));

    $user = $users->findById($userId);
    $stored = $credentials->findByUserId($userId);

    expect($user)->not->toBeNull()
        ->and($user->email()->value())->toBe('ada@nuvora.test')
        ->and($user->createdAt())->toBe($createdAt)
        ->and($user->role())->toEqual(Role::customer())
        ->and($stored)->not->toBeNull()
        ->and($stored->hashedPassword()->value())->toBe('hashed:secret-secret')
        ->and($stored->hashedPassword()->value())->not->toBe('secret-secret');
});

it('does not create a user when the password is invalid', function () {
    $users = new InMemoryUserRepository();
    $handler = new RegisterAccountCommandHandler(
        new RegisterUserCommandHandler($users, new FixedClock(new DateTimeImmutable('2026-08-20T12:00:00+00:00'))),
        new InMemoryCredentialsRepository(),
        new FakePasswordHasher(),
    );

    expect(fn () => $handler->handle(new RegisterAccountCommand(
        email: 'ada@nuvora.test',
        password: 'short',
    )))->toThrow(InvalidPassword::class)
        ->and($users->findByEmail(Email::fromString('ada@nuvora.test')))->toBeNull();
});

it('rejects a duplicate email', function () {
    $handler = new RegisterAccountCommandHandler(
        new RegisterUserCommandHandler(
            new InMemoryUserRepository(),
            new FixedClock(new DateTimeImmutable('2026-08-20T12:00:00+00:00')),
        ),
        new InMemoryCredentialsRepository(),
        new FakePasswordHasher(),
    );

    $handler->handle(new RegisterAccountCommand(
        email: 'ada@nuvora.test',
        password: 'secret-secret',
    ));
    $handler->handle(new RegisterAccountCommand(
        email: 'ADA@nuvora.test',
        password: 'another-secret',
    ));
})->throws(EmailAlreadyRegistered::class);
