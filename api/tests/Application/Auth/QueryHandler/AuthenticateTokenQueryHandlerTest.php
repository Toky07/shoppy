<?php

declare(strict_types=1);

use App\Auth\Application\Query\AuthenticateTokenQuery;
use App\Auth\Application\QueryHandler\AuthenticateTokenQueryHandler;
use App\Auth\Domain\Entity\AccessToken;
use App\Auth\Domain\Exception\Unauthenticated;
use App\Auth\Infrastructure\Persistence\InMemoryAccessTokenRepository;
use App\Tests\Doubles\FakeTokenGenerator;
use App\Tests\Doubles\FixedClock;
use App\User\Domain\ValueObject\UserId;

it('authenticates a valid access token', function () {
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

    $authenticated = (new AuthenticateTokenQueryHandler($tokens, $generator, new FixedClock($now)))
        ->handle(new AuthenticateTokenQuery('test-access-token'));

    expect($authenticated->value())->toBe($userId->value());
});

it('rejects an unknown token', function () {
    (new AuthenticateTokenQueryHandler(
        new InMemoryAccessTokenRepository(),
        new FakeTokenGenerator(),
        new FixedClock(new DateTimeImmutable('2026-08-20T12:00:00+00:00')),
    ))->handle(new AuthenticateTokenQuery('unknown-token'));
})->throws(Unauthenticated::class);

it('rejects an expired token', function () {
    $tokens = new InMemoryAccessTokenRepository();
    $generator = new FakeTokenGenerator();
    $generated = $generator->generate();
    $issuedAt = new DateTimeImmutable('2026-08-01T12:00:00+00:00');

    $tokens->save(AccessToken::issue(
        UserId::fromString('11111111-1111-4111-8111-111111111111'),
        $generated->hash,
        $issuedAt->modify('+7 days'),
        $issuedAt,
    ));

    (new AuthenticateTokenQueryHandler(
        $tokens,
        $generator,
        new FixedClock(new DateTimeImmutable('2026-08-20T12:00:00+00:00')),
    ))->handle(new AuthenticateTokenQuery('test-access-token'));
})->throws(Unauthenticated::class);
