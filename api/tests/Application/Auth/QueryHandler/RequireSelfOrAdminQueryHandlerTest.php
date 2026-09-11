<?php

declare(strict_types=1);

use App\Auth\Application\Query\RequireSelfOrAdminQuery;
use App\Auth\Application\QueryHandler\AuthenticateTokenQueryHandler;
use App\Auth\Application\QueryHandler\RequireSelfOrAdminQueryHandler;
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

function requireSelfOrAdminHandler(
    InMemoryUserRepository $users,
    InMemoryAccessTokenRepository $tokens,
    DateTimeImmutable $now = new DateTimeImmutable('2026-08-20T12:00:00+00:00'),
): RequireSelfOrAdminQueryHandler {
    return new RequireSelfOrAdminQueryHandler(
        new AuthenticateTokenQueryHandler($tokens, new FakeTokenGenerator(), new FixedClock($now)),
        $users,
    );
}

function selfOrAdminIssuedToken(
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

it('allows a user to access their own id', function () {
    $users = new InMemoryUserRepository();
    $tokens = new InMemoryAccessTokenRepository();
    $userId = UserId::fromString('11111111-1111-4111-8111-111111111111');
    $users->save(User::register(
        $userId,
        Email::fromString('ada@nuvora.test'),
        new DateTimeImmutable('2026-08-20T12:00:00+00:00'),
    ));
    selfOrAdminIssuedToken($tokens, $userId);

    $authenticated = requireSelfOrAdminHandler($users, $tokens)->handle(new RequireSelfOrAdminQuery(
        'test-access-token',
        $userId->value(),
    ));

    expect($authenticated->value())->toBe($userId->value());
});

it('allows an admin to access another user id', function () {
    $users = new InMemoryUserRepository();
    $tokens = new InMemoryAccessTokenRepository();
    $adminId = UserId::fromString('11111111-1111-4111-8111-111111111111');
    $targetId = UserId::fromString('22222222-2222-4222-8222-222222222222');
    $admin = User::register(
        $adminId,
        Email::fromString('admin@nuvora.test'),
        new DateTimeImmutable('2026-08-20T12:00:00+00:00'),
    );
    $admin->assignRole(Role::admin());
    $users->save($admin);
    $users->save(User::register(
        $targetId,
        Email::fromString('ada@nuvora.test'),
        new DateTimeImmutable('2026-08-20T12:00:00+00:00'),
    ));
    selfOrAdminIssuedToken($tokens, $adminId);

    $authenticated = requireSelfOrAdminHandler($users, $tokens)->handle(new RequireSelfOrAdminQuery(
        'test-access-token',
        $targetId->value(),
    ));

    expect($authenticated->value())->toBe($adminId->value());
});

it('rejects a customer accessing another user id', function () {
    $users = new InMemoryUserRepository();
    $tokens = new InMemoryAccessTokenRepository();
    $userId = UserId::fromString('11111111-1111-4111-8111-111111111111');
    $users->save(User::register(
        $userId,
        Email::fromString('ada@nuvora.test'),
        new DateTimeImmutable('2026-08-20T12:00:00+00:00'),
    ));
    selfOrAdminIssuedToken($tokens, $userId);

    requireSelfOrAdminHandler($users, $tokens)->handle(new RequireSelfOrAdminQuery(
        'test-access-token',
        '22222222-2222-4222-8222-222222222222',
    ));
})->throws(Forbidden::class);

it('rejects an unknown token as unauthenticated', function () {
    requireSelfOrAdminHandler(new InMemoryUserRepository(), new InMemoryAccessTokenRepository())
        ->handle(new RequireSelfOrAdminQuery(
            'unknown-token',
            '11111111-1111-4111-8111-111111111111',
        ));
})->throws(Unauthenticated::class);
