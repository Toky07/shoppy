<?php

declare(strict_types=1);

use App\Auth\Application\Command\LoginCommand;
use App\Auth\Application\Command\RegisterAccountCommand;
use App\Auth\Application\CommandHandler\LoginCommandHandler;
use App\Auth\Application\CommandHandler\RegisterAccountCommandHandler;
use App\Auth\Domain\Exception\InvalidCredentials;
use App\Auth\Domain\ValueObject\TokenHash;
use App\Auth\Infrastructure\Persistence\InMemoryAccessTokenRepository;
use App\Auth\Infrastructure\Persistence\InMemoryCredentialsRepository;
use App\Tests\Doubles\FakePasswordHasher;
use App\Tests\Doubles\FakeTokenGenerator;
use App\Tests\Doubles\FixedClock;
use App\User\Application\CommandHandler\RegisterUserCommandHandler;
use App\User\Infrastructure\Persistence\InMemoryUserRepository;

function loginHandler(
    InMemoryUserRepository $users,
    InMemoryCredentialsRepository $credentials,
    InMemoryAccessTokenRepository $tokens = new InMemoryAccessTokenRepository(),
    DateTimeImmutable $now = new DateTimeImmutable('2026-08-20T12:00:00+00:00'),
): LoginCommandHandler {
    return new LoginCommandHandler(
        $users,
        $credentials,
        new FakePasswordHasher(),
        $tokens,
        new FakeTokenGenerator(),
        new FixedClock($now),
    );
}

function registeredAccount(
    InMemoryUserRepository $users,
    InMemoryCredentialsRepository $credentials,
    string $email = 'Ada@Nuvora.test',
    string $password = 'secret-secret',
): void {
    $register = new RegisterAccountCommandHandler(
        new RegisterUserCommandHandler(
            $users,
            new FixedClock(new DateTimeImmutable('2026-08-20T12:00:00+00:00')),
        ),
        $credentials,
        new FakePasswordHasher(),
    );

    $register->handle(new RegisterAccountCommand($email, $password));
}

it('logs in with valid credentials and issues a hashed access token', function () {
    $users = new InMemoryUserRepository();
    $credentials = new InMemoryCredentialsRepository();
    $tokens = new InMemoryAccessTokenRepository();
    $now = new DateTimeImmutable('2026-08-20T12:00:00+00:00');
    registeredAccount($users, $credentials);

    $result = loginHandler($users, $credentials, $tokens, $now)->handle(new LoginCommand(
        email: 'ADA@nuvora.test',
        password: 'secret-secret',
    ));

    $user = $users->findById($result->userId);
    $stored = $tokens->findByHash(TokenHash::fromHash(hash('sha256', 'test-access-token')));

    expect($user)->not->toBeNull()
        ->and($user->email()->value())->toBe('ada@nuvora.test')
        ->and($result->accessToken)->toBe('test-access-token')
        ->and($stored)->not->toBeNull()
        ->and($stored->userId()->value())->toBe($result->userId->value())
        ->and($stored->hash()->value())->not->toBe('test-access-token')
        ->and($stored->isExpired($now))->toBeFalse()
        ->and($stored->isExpired($now->modify('+7 days')))->toBeTrue();
});

it('does not issue a token when the password is wrong', function () {
    $users = new InMemoryUserRepository();
    $credentials = new InMemoryCredentialsRepository();
    $tokens = new InMemoryAccessTokenRepository();
    registeredAccount($users, $credentials);

    expect(fn () => loginHandler($users, $credentials, $tokens)->handle(new LoginCommand(
        email: 'ada@nuvora.test',
        password: 'wrong-password',
    )))->toThrow(InvalidCredentials::class)
        ->and($tokens->findByHash(TokenHash::fromHash(hash('sha256', 'test-access-token'))))->toBeNull();
});

it('rejects an unknown email with the same failure', function () {
    loginHandler(new InMemoryUserRepository(), new InMemoryCredentialsRepository())->handle(
        new LoginCommand(email: 'unknown@nuvora.test', password: 'secret-secret'),
    );
})->throws(InvalidCredentials::class);

it('rejects a too short password as invalid credentials', function () {
    $users = new InMemoryUserRepository();
    $credentials = new InMemoryCredentialsRepository();
    registeredAccount($users, $credentials);

    loginHandler($users, $credentials)->handle(new LoginCommand(
        email: 'ada@nuvora.test',
        password: 'short',
    ));
})->throws(InvalidCredentials::class);
