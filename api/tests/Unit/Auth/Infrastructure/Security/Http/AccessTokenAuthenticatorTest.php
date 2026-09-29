<?php

declare(strict_types=1);

use App\Auth\Application\QueryHandler\AuthenticateTokenQueryHandler;
use App\Auth\Domain\Entity\AccessToken;
use App\Auth\Infrastructure\Persistence\InMemoryAccessTokenRepository;
use App\Auth\Infrastructure\Security\AuthenticatedUser;
use App\Auth\Infrastructure\Security\Http\AccessTokenAuthenticator;
use App\Auth\Infrastructure\Security\Http\InvalidAccessTokenAuthenticationException;
use App\Auth\Presentation\Http\CurrentUser;
use App\Tests\Doubles\FakeTokenGenerator;
use App\Tests\Doubles\FixedClock;
use App\User\Domain\Entity\User;
use App\User\Domain\ValueObject\Email;
use App\User\Domain\ValueObject\Role;
use App\User\Domain\ValueObject\UserId;
use App\User\Infrastructure\Persistence\InMemoryUserRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Token\PostAuthenticationToken;

function accessTokenAuthenticator(
    InMemoryAccessTokenRepository $tokens,
    InMemoryUserRepository $users,
    DateTimeImmutable $now,
): AccessTokenAuthenticator {
    return new AccessTokenAuthenticator(
        new AuthenticateTokenQueryHandler($tokens, new FakeTokenGenerator(), new FixedClock($now)),
        $users,
    );
}

it('supports requests that carry an access credential', function () {
    $now = new DateTimeImmutable('2026-08-20T12:00:00+00:00');
    $authenticator = accessTokenAuthenticator(
        new InMemoryAccessTokenRepository(),
        new InMemoryUserRepository(),
        $now,
    );

    $withBearer = Request::create('/products', 'GET');
    $withBearer->headers->set('Authorization', 'Bearer secret');

    expect($authenticator->supports($withBearer))->toBeTrue()
        ->and($authenticator->supports(Request::create('/products')))->toBeFalse();
});

it('authenticates a valid bearer token and loads the user', function () {
    $tokens = new InMemoryAccessTokenRepository();
    $generator = new FakeTokenGenerator();
    $generated = $generator->generate();
    $userId = UserId::fromString('11111111-1111-4111-8111-111111111111');
    $now = new DateTimeImmutable('2026-08-20T12:00:00+00:00');

    $tokens->save(AccessToken::issue(
        $userId,
        $generated->hash,
        $now->modify('+7 days'),
        $now,
    ));

    $users = new InMemoryUserRepository();
    $users->save(User::register(
        $userId,
        Email::fromString('ada@example.test'),
        $now,
        Role::admin(),
    ));

    $authenticator = accessTokenAuthenticator($tokens, $users, $now);
    $request = Request::create('/cart', 'GET');
    $request->headers->set('Authorization', 'Bearer test-access-token');

    $passport = $authenticator->authenticate($request);
    $user = $passport->getBadge(UserBadge::class)->getUser();

    expect($user)->toBeInstanceOf(AuthenticatedUser::class)
        ->and($user->id())->toBe($userId->value())
        ->and($user->getRoles())->toContain(AuthenticatedUser::ROLE_ADMIN);
});

it('rejects invalid tokens without treating them as anonymous at authenticate time', function () {
    $now = new DateTimeImmutable('2026-08-20T12:00:00+00:00');
    $authenticator = accessTokenAuthenticator(
        new InMemoryAccessTokenRepository(),
        new InMemoryUserRepository(),
        $now,
    );
    $request = Request::create('/cart', 'GET');
    $request->headers->set('Authorization', 'Bearer bad-token');

    $authenticator->authenticate($request);
})->throws(InvalidAccessTokenAuthenticationException::class);

it('syncs current user attributes after successful authentication', function () {
    $userId = '11111111-1111-4111-8111-111111111111';
    $authenticatedUser = AuthenticatedUser::fromDomainRole($userId, Role::customer(), 'session-token');
    $token = new PostAuthenticationToken($authenticatedUser, 'main', $authenticatedUser->getRoles());
    $request = Request::create('/auth/me', 'GET');

    $authenticator = accessTokenAuthenticator(
        new InMemoryAccessTokenRepository(),
        new InMemoryUserRepository(),
        new DateTimeImmutable('2026-08-20T12:00:00+00:00'),
    );

    expect($authenticator->onAuthenticationSuccess($request, $token, 'main'))->toBeNull()
        ->and($request->attributes->get(CurrentUser::USER_ID))->toBe($userId)
        ->and($request->attributes->get(CurrentUser::ROLE))->toBe(Role::customer()->value())
        ->and($request->attributes->get(CurrentUser::TOKEN))->toBe('session-token');
});

it('returns null on authentication failure so optional routes stay anonymous', function () {
    $authenticator = accessTokenAuthenticator(
        new InMemoryAccessTokenRepository(),
        new InMemoryUserRepository(),
        new DateTimeImmutable('2026-08-20T12:00:00+00:00'),
    );

    expect($authenticator->onAuthenticationFailure(
        Request::create('/products'),
        new InvalidAccessTokenAuthenticationException(),
    ))->toBeNull();
});
