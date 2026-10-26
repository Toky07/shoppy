<?php

declare(strict_types=1);

use App\Auth\Application\Command\PurgeExpiredAccessTokensCommand;
use App\Auth\Application\CommandHandler\PurgeExpiredAccessTokensCommandHandler;
use App\Auth\Domain\Entity\AccessToken;
use App\Auth\Domain\ValueObject\AccessTokenKind;
use App\Auth\Domain\ValueObject\TokenHash;
use App\Auth\Infrastructure\Persistence\InMemoryAccessTokenRepository;
use App\Tests\Doubles\FixedClock;
use App\User\Domain\ValueObject\UserId;

it('deletes expired access and refresh tokens and keeps live ones', function () {
    $tokens = new InMemoryAccessTokenRepository();
    $userId = UserId::fromString('11111111-1111-4111-8111-111111111111');
    $now = new DateTimeImmutable('2026-08-20T12:00:00+00:00');
    $expiredAccess = TokenHash::fromHash(str_repeat('a', 64));
    $expiredRefresh = TokenHash::fromHash(str_repeat('b', 64));
    $liveAccess = TokenHash::fromHash(str_repeat('c', 64));

    $tokens->save(AccessToken::issue($userId, $expiredAccess, $now->modify('-1 hour'), $now->modify('-2 hours')));
    $tokens->save(AccessToken::issue(
        $userId,
        $expiredRefresh,
        $now->modify('-1 minute'),
        $now->modify('-8 days'),
        AccessTokenKind::refresh(),
    ));
    $tokens->save(AccessToken::issue($userId, $liveAccess, $now->modify('+1 hour'), $now));

    $deleted = (new PurgeExpiredAccessTokensCommandHandler($tokens, new FixedClock($now)))
        ->handle(new PurgeExpiredAccessTokensCommand());

    expect($deleted)->toBe(2)
        ->and($tokens->findByHash($expiredAccess))->toBeNull()
        ->and($tokens->findByHash($expiredRefresh))->toBeNull()
        ->and($tokens->findByHash($liveAccess))->not->toBeNull();
});

it('returns zero when every token is still valid', function () {
    $tokens = new InMemoryAccessTokenRepository();
    $now = new DateTimeImmutable('2026-08-20T12:00:00+00:00');
    $hash = TokenHash::fromHash(str_repeat('d', 64));
    $tokens->save(AccessToken::issue(
        UserId::fromString('11111111-1111-4111-8111-111111111111'),
        $hash,
        $now->modify('+1 hour'),
        $now,
    ));

    $deleted = (new PurgeExpiredAccessTokensCommandHandler($tokens, new FixedClock($now)))
        ->handle(new PurgeExpiredAccessTokensCommand());

    expect($deleted)->toBe(0)
        ->and($tokens->findByHash($hash))->not->toBeNull();
});
