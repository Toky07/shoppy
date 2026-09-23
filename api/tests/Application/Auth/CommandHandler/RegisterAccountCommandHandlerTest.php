<?php

declare(strict_types=1);

use App\Auth\Application\AccountNotifier;
use App\Auth\Application\Command\RegisterAccountCommand;
use App\Auth\Application\CommandHandler\RegisterAccountCommandHandler;
use App\Auth\Application\IssueAccountToken;
use App\Auth\Domain\Exception\InvalidPassword;
use App\Auth\Infrastructure\Persistence\InMemoryAccountTokenRepository;
use App\Auth\Infrastructure\Persistence\InMemoryCredentialsRepository;
use App\Tests\Doubles\FakePasswordHasher;
use App\Tests\Doubles\FakeTokenGenerator;
use App\Tests\Doubles\FixedClock;
use App\Tests\Doubles\RecordingEventBus;
use App\User\Application\CommandHandler\RegisterUserCommandHandler;
use App\User\Domain\ValueObject\Email;
use App\User\Domain\ValueObject\Role;
use App\User\Infrastructure\Persistence\InMemoryUserRepository;

function registerAccountHandler(
    InMemoryUserRepository $users,
    ?InMemoryCredentialsRepository $credentials = null,
    ?DateTimeImmutable $now = null,
): RegisterAccountCommandHandler {
    $clock = new FixedClock($now ?? new DateTimeImmutable('2026-08-20T12:00:00+00:00'));

    return new RegisterAccountCommandHandler(
        new RegisterUserCommandHandler($users, $clock),
        $credentials ?? new InMemoryCredentialsRepository(),
        new FakePasswordHasher(),
        $users,
        new IssueAccountToken(new InMemoryAccountTokenRepository(), new FakeTokenGenerator(), $clock),
        new AccountNotifier(new RecordingEventBus(), 'http://localhost:5173'),
    );
}

it('registers a user and stores hashed credentials', function () {
    $users = new InMemoryUserRepository();
    $credentials = new InMemoryCredentialsRepository();
    $createdAt = new DateTimeImmutable('2026-08-20T12:00:00+00:00');
    $handler = registerAccountHandler($users, $credentials, $createdAt);

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
    $handler = registerAccountHandler($users);

    expect(fn () => $handler->handle(new RegisterAccountCommand(
        email: 'ada@nuvora.test',
        password: 'short',
    )))->toThrow(InvalidPassword::class)
        ->and($users->findByEmail(Email::fromString('ada@nuvora.test')))->toBeNull();
});

it('returns null when the email is already registered', function () {
    $users = new InMemoryUserRepository();
    $handler = registerAccountHandler($users);

    $created = $handler->handle(new RegisterAccountCommand(
        email: 'ada@nuvora.test',
        password: 'secret-secret',
    ));
    $duplicate = $handler->handle(new RegisterAccountCommand(
        email: 'ADA@nuvora.test',
        password: 'another-secret',
    ));

    expect($duplicate)->toBeNull()
        ->and($created)->not->toBeNull()
        ->and($users->findByEmail(Email::fromString('ada@nuvora.test'))->id()->value())->toBe($created->value());
});
