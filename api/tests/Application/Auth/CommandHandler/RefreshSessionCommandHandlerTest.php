<?php

declare(strict_types=1);

use App\Auth\Application\Command\RefreshSessionCommand;
use App\Auth\Application\CommandHandler\RefreshSessionCommandHandler;
use App\Auth\Application\IssueSession;
use App\Auth\Domain\Exception\Unauthenticated;
use App\Auth\Domain\ValueObject\AccessTokenKind;
use App\Auth\Domain\ValueObject\TokenHash;
use App\Auth\Infrastructure\Persistence\InMemoryAccessTokenRepository;
use App\Tests\Doubles\FakeTokenGenerator;
use App\Tests\Doubles\FixedClock;
use App\User\Domain\ValueObject\UserId;

function refreshHandler(
    InMemoryAccessTokenRepository $tokens,
    DateTimeImmutable $now = new DateTimeImmutable('2026-08-20T12:00:00+00:00'),
): RefreshSessionCommandHandler {
    $clock = new FixedClock($now);

    return new RefreshSessionCommandHandler(
        $tokens,
        new FakeTokenGenerator(),
        $clock,
        new IssueSession($tokens, new FakeTokenGenerator('rotated-token'), $clock),
    );
}

it('rotates a valid refresh token into a new session pair', function () {
    $tokens = new InMemoryAccessTokenRepository();
    $now = new DateTimeImmutable('2026-08-20T12:00:00+00:00');
    $sessions = new IssueSession($tokens, new FakeTokenGenerator(), new FixedClock($now));
    $issued = $sessions->issue(UserId::fromString('11111111-1111-4111-8111-111111111111'));

    $rotated = refreshHandler($tokens, $now)->handle(new RefreshSessionCommand($issued->refreshToken));

    expect($rotated->accessToken)->toBe('rotated-token')
        ->and($rotated->refreshToken)->toBe('rotated-token-1')
        ->and($tokens->findByHash(TokenHash::fromHash(hash('sha256', $issued->refreshToken))))->toBeNull()
        ->and($tokens->findByHash(TokenHash::fromHash(hash('sha256', $rotated->accessToken)))?->kind())
        ->toEqual(AccessTokenKind::access())
        ->and($tokens->findByHash(TokenHash::fromHash(hash('sha256', $rotated->refreshToken)))?->kind())
        ->toEqual(AccessTokenKind::refresh());
});

it('rejects an access token used as a refresh token', function () {
    $tokens = new InMemoryAccessTokenRepository();
    $now = new DateTimeImmutable('2026-08-20T12:00:00+00:00');
    $issued = (new IssueSession($tokens, new FakeTokenGenerator(), new FixedClock($now)))
        ->issue(UserId::fromString('11111111-1111-4111-8111-111111111111'));

    refreshHandler($tokens, $now)->handle(new RefreshSessionCommand($issued->accessToken));
})->throws(Unauthenticated::class);

it('rejects an expired refresh token', function () {
    $tokens = new InMemoryAccessTokenRepository();
    $issuedAt = new DateTimeImmutable('2026-08-01T12:00:00+00:00');
    $issued = (new IssueSession($tokens, new FakeTokenGenerator(), new FixedClock($issuedAt)))
        ->issue(UserId::fromString('11111111-1111-4111-8111-111111111111'));

    refreshHandler($tokens, new DateTimeImmutable('2026-08-20T12:00:00+00:00'))
        ->handle(new RefreshSessionCommand($issued->refreshToken));
})->throws(Unauthenticated::class);
