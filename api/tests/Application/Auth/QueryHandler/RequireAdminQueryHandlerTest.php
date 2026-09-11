<?php

declare(strict_types=1);

use App\Auth\Application\Query\RequireAdminQuery;
use App\Auth\Application\QueryHandler\AuthenticateTokenQueryHandler;
use App\Auth\Application\QueryHandler\RequireAdminQueryHandler;
use App\Auth\Domain\Entity\AccessToken;
use App\Auth\Domain\Exception\Forbidden;
use App\Auth\Domain\Exception\Unauthenticated;
use App\Auth\Infrastructure\Persistence\InMemoryAccessTokenRepository;
use App\Tests\Doubles\FakeTokenGenerator;
use App\Tests\Doubles\FixedClock;
use App\User\Domain\Entity\User;
use App\User\Domain\ValueObject\Email;
use App\User\Domain\ValueObject\Role;
use App\User\Domain\ValueObject\UserId;
use App\User\Infrastructure\Persistence\InMemoryUserRepository;

function requireAdminHandler(
    InMemoryUserRepository $users,
    InMemoryAccessTokenRepository $tokens,
    DateTimeImmutable $now = new DateTimeImmutable('2026-08-20T12:00:00+00:00'),
): RequireAdminQueryHandler {
    return new RequireAdminQueryHandler(
        new AuthenticateTokenQueryHandler($tokens, new FakeTokenGenerator(), new FixedClock($now)),
        $users,
    );
}

function issuedTokenFor(
    InMemoryAccessTokenRepository $tokens,
    UserId $userId,
    DateTimeImmutable $now = new DateTimeImmutable('2026-08-20T12:00:00+00:00'),
): void {
    $generated = (new FakeTokenGenerator())->generate();
    $tokens->save(AccessToken::issue(
        $userId,
        $generated->hash,
        $now->modify('+7 days'),
        $now,
    ));
}

it('allows an admin access token', function () {
    $users = new InMemoryUserRepository();
    $tokens = new InMemoryAccessTokenRepository();
    $userId = UserId::fromString('11111111-1111-4111-8111-111111111111');
    $user = User::register(
        $userId,
        Email::fromString('admin@nuvora.test'),
        new DateTimeImmutable('2026-08-20T12:00:00+00:00'),
    );
    $user->assignRole(Role::admin());
    $users->save($user);
    issuedTokenFor($tokens, $userId);

    $authenticated = requireAdminHandler($users, $tokens)->handle(new RequireAdminQuery('test-access-token'));

    expect($authenticated->value())->toBe($userId->value());
});

it('rejects a customer access token', function () {
    $users = new InMemoryUserRepository();
    $tokens = new InMemoryAccessTokenRepository();
    $userId = UserId::fromString('11111111-1111-4111-8111-111111111111');
    $users->save(User::register(
        $userId,
        Email::fromString('ada@nuvora.test'),
        new DateTimeImmutable('2026-08-20T12:00:00+00:00'),
    ));
    issuedTokenFor($tokens, $userId);

    requireAdminHandler($users, $tokens)->handle(new RequireAdminQuery('test-access-token'));
})->throws(Forbidden::class);

it('rejects an unknown token as unauthenticated', function () {
    requireAdminHandler(new InMemoryUserRepository(), new InMemoryAccessTokenRepository())
        ->handle(new RequireAdminQuery('unknown-token'));
})->throws(Unauthenticated::class);

it('rejects a missing user as unauthenticated', function () {
    $tokens = new InMemoryAccessTokenRepository();
    issuedTokenFor($tokens, UserId::fromString('11111111-1111-4111-8111-111111111111'));

    requireAdminHandler(new InMemoryUserRepository(), $tokens)
        ->handle(new RequireAdminQuery('test-access-token'));
})->throws(Unauthenticated::class);
