<?php

declare(strict_types=1);

use App\Auth\Application\Command\LogoutCommand;
use App\Auth\Application\CommandHandler\LogoutCommandHandler;
use App\Auth\Domain\Entity\AccessToken;
use App\Auth\Domain\Exception\Unauthenticated;
use App\Auth\Domain\ValueObject\TokenHash;
use App\Auth\Infrastructure\Persistence\InMemoryAccessTokenRepository;
use App\Tests\Doubles\FakeTokenGenerator;
use App\Tests\Doubles\FixedClock;
use App\User\Domain\ValueObject\UserId;

function logoutHandler(
    InMemoryAccessTokenRepository $tokens = new InMemoryAccessTokenRepository(),
    DateTimeImmutable $now = new DateTimeImmutable('2026-08-20T12:00:00+00:00'),
): LogoutCommandHandler {
    return new LogoutCommandHandler($tokens, new FakeTokenGenerator(), new FixedClock($now));
}

it('revokes a valid access token', function () {
    $tokens = new InMemoryAccessTokenRepository();
    $generator = new FakeTokenGenerator();
    $generated = $generator->generate();
    $now = new DateTimeImmutable('2026-08-20T12:00:00+00:00');
    $hash = TokenHash::fromHash(hash('sha256', 'test-access-token'));

    $tokens->save(AccessToken::issue(
        UserId::fromString('11111111-1111-4111-8111-111111111111'),
        $generated->hash,
        $now->modify('+7 days'),
        $now,
    ));

    logoutHandler($tokens, $now)->handle(new LogoutCommand('test-access-token'));

    expect($tokens->findByHash($hash))->toBeNull();
});

it('rejects an unknown token', function () {
    logoutHandler()->handle(new LogoutCommand('unknown-token'));
})->throws(Unauthenticated::class);

it('rejects an expired token without revoking it', function () {
    $tokens = new InMemoryAccessTokenRepository();
    $generator = new FakeTokenGenerator();
    $generated = $generator->generate();
    $issuedAt = new DateTimeImmutable('2026-08-01T12:00:00+00:00');
    $now = new DateTimeImmutable('2026-08-20T12:00:00+00:00');
    $hash = $generated->hash;

    $tokens->save(AccessToken::issue(
        UserId::fromString('11111111-1111-4111-8111-111111111111'),
        $hash,
        $issuedAt->modify('+7 days'),
        $issuedAt,
    ));

    expect(fn () => logoutHandler($tokens, $now)->handle(new LogoutCommand('test-access-token')))
        ->toThrow(Unauthenticated::class)
        ->and($tokens->findByHash($hash))->not->toBeNull();
});
