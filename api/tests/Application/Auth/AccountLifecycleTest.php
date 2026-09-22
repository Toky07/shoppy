<?php

declare(strict_types=1);

use App\Auth\Application\AccountNotifier;
use App\Auth\Application\Command\ChangePasswordCommand;
use App\Auth\Application\Command\ConfirmEmailChangeCommand;
use App\Auth\Application\Command\DeleteAccountCommand;
use App\Auth\Application\Command\LoginCommand;
use App\Auth\Application\Command\LogoutAllCommand;
use App\Auth\Application\Command\RegisterAccountCommand;
use App\Auth\Application\Command\RequestEmailChangeCommand;
use App\Auth\Application\Command\RequestPasswordResetCommand;
use App\Auth\Application\Command\ResetPasswordCommand;
use App\Auth\Application\Command\VerifyEmailCommand;
use App\Auth\Application\CommandHandler\ChangePasswordCommandHandler;
use App\Auth\Application\CommandHandler\ConfirmEmailChangeCommandHandler;
use App\Auth\Application\CommandHandler\DeleteAccountCommandHandler;
use App\Auth\Application\CommandHandler\LoginCommandHandler;
use App\Auth\Application\CommandHandler\LogoutAllCommandHandler;
use App\Auth\Application\CommandHandler\RegisterAccountCommandHandler;
use App\Auth\Application\CommandHandler\RequestEmailChangeCommandHandler;
use App\Auth\Application\CommandHandler\RequestPasswordResetCommandHandler;
use App\Auth\Application\CommandHandler\ResetPasswordCommandHandler;
use App\Auth\Application\CommandHandler\VerifyEmailCommandHandler;
use App\Auth\Application\CurrentPassword;
use App\Auth\Application\IssueAccountToken;
use App\Auth\Domain\Entity\AccountToken;
use App\Auth\Domain\Exception\InvalidAccountToken;
use App\Auth\Domain\Exception\LastAdminAccount;
use App\Auth\Domain\ValueObject\AccountTokenPurpose;
use App\Auth\Domain\ValueObject\TokenHash;
use App\Auth\Infrastructure\Persistence\InMemoryAccessTokenRepository;
use App\Auth\Infrastructure\Persistence\InMemoryAccountTokenRepository;
use App\Auth\Infrastructure\Persistence\InMemoryCredentialsRepository;
use App\Auth\Infrastructure\Security\RandomTokenGenerator;
use App\Email\Domain\Event\EmailRequested;
use App\Tests\Doubles\FakePasswordHasher;
use App\Tests\Doubles\FakeTokenGenerator;
use App\Tests\Doubles\FixedClock;
use App\Tests\Doubles\RecordingEventBus;
use App\User\Application\CommandHandler\RegisterUserCommandHandler;
use App\User\Domain\ValueObject\Role;
use App\User\Domain\ValueObject\UserId;
use App\User\Infrastructure\Persistence\InMemoryUserRepository;

function accountContext(): array
{
    $users = new InMemoryUserRepository();
    $credentials = new InMemoryCredentialsRepository();
    $accessTokens = new InMemoryAccessTokenRepository();
    $accountTokens = new InMemoryAccountTokenRepository();
    $events = new RecordingEventBus();
    $now = new DateTimeImmutable('2026-08-20T12:00:00+00:00');
    $clock = new FixedClock($now);
    $issuer = new IssueAccountToken($accountTokens, new RandomTokenGenerator(), $clock);
    $notifier = new AccountNotifier($events, 'http://localhost:5173');
    $passwords = new CurrentPassword($credentials, new FakePasswordHasher());

    return [
        $users,
        $credentials,
        $accessTokens,
        $events,
        $clock,
        new RegisterAccountCommandHandler(
            new RegisterUserCommandHandler($users, $clock),
            $credentials,
            new FakePasswordHasher(),
            $users,
            $issuer,
            $notifier,
        ),
        new RequestPasswordResetCommandHandler($users, $issuer, $notifier),
        new ResetPasswordCommandHandler($issuer, $users, $credentials, new FakePasswordHasher(), $accessTokens),
        new VerifyEmailCommandHandler($issuer, $users, $clock),
        new ChangePasswordCommandHandler($passwords, $credentials, new FakePasswordHasher(), $accessTokens),
        new RequestEmailChangeCommandHandler($users, $passwords, $issuer, $notifier),
        new ConfirmEmailChangeCommandHandler($issuer, $users, $accessTokens, $clock),
        new DeleteAccountCommandHandler($users, $passwords, $credentials, $accessTokens, $accountTokens, $clock),
        new LogoutAllCommandHandler($accessTokens),
        new LoginCommandHandler($users, $credentials, new FakePasswordHasher(), $accessTokens, new FakeTokenGenerator(), $clock),
    ];
}

function tokenFromLastEmail(RecordingEventBus $events): string
{
    $event = $events->published[array_key_last($events->published)];
    expect($event)->toBeInstanceOf(EmailRequested::class);
    preg_match('/token=([a-f0-9]+)/', $event->body->text(), $matches);

    return $matches[1];
}

it('rejects an expired or reused account token', function () {
    $created = new DateTimeImmutable('2026-08-20T12:00:00+00:00');
    $token = AccountToken::issue(
        TokenHash::fromHash(str_repeat('a', 64)),
        UserId::fromString('11111111-1111-4111-8111-111111111111'),
        AccountTokenPurpose::passwordReset(),
        $created->modify('+1 hour'),
        $created,
    );

    expect(fn () => $token->consume($created->modify('+1 hour'), AccountTokenPurpose::passwordReset()))
        ->toThrow(InvalidAccountToken::class);

    $token->consume($created, AccountTokenPurpose::passwordReset());

    expect(fn () => $token->consume($created, AccountTokenPurpose::passwordReset()))
        ->toThrow(InvalidAccountToken::class);
});

it('resets a password from a single-use email link and revokes sessions', function () {
    [
        $users,
        $credentials,
        $accessTokens,
        $events,
        ,
        $register,
        $requestReset,
        $reset,
        ,
        ,
        ,
        ,
        ,
        ,
        $login,
    ] = accountContext();
    $userId = $register->handle(new RegisterAccountCommand('ada@shoppy.test', 'secret-secret'));
    $login->handle(new LoginCommand('ada@shoppy.test', 'secret-secret'));
    expect($accessTokens->findByHash(TokenHash::fromHash(hash('sha256', 'test-access-token'))))->not->toBeNull();

    $requestReset->handle(new RequestPasswordResetCommand('missing@shoppy.test'));
    expect($events->published)->toHaveCount(1);

    $requestReset->handle(new RequestPasswordResetCommand('Ada@shoppy.test'));
    $reset->handle(new ResetPasswordCommand(tokenFromLastEmail($events), 'brand-new-secret'));

    expect($accessTokens->findByHash(TokenHash::fromHash(hash('sha256', 'test-access-token'))))->toBeNull()
        ->and($credentials->findByUserId($userId)?->hashedPassword()->value())->toBe('hashed:brand-new-secret');

    expect(fn () => $reset->handle(new ResetPasswordCommand(tokenFromLastEmail($events), 'another-secret-1')))
        ->toThrow(InvalidAccountToken::class);
});

it('verifies the address from the registration email', function () {
    [$users, , , $events, , $register, , , $verify] = accountContext();
    $userId = $register->handle(new RegisterAccountCommand('ada@shoppy.test', 'secret-secret'));

    expect($users->findById($userId)?->isEmailVerified())->toBeFalse()
        ->and($events->published[0]->subject->value())->toBe('Confirmez votre adresse email');

    $verify->handle(new VerifyEmailCommand(tokenFromLastEmail($events)));

    expect($users->findById($userId)?->isEmailVerified())->toBeTrue();
});

it('changes the email after the new address confirms the link', function () {
    [$users, , $accessTokens, $events, , $register, , , , , $requestChange, $confirm, , , $login] = accountContext();
    $userId = $register->handle(new RegisterAccountCommand('ada@shoppy.test', 'secret-secret'));
    $login->handle(new LoginCommand('ada@shoppy.test', 'secret-secret'));

    $requestChange->handle(new RequestEmailChangeCommand($userId->value(), 'ada.new@shoppy.test', 'secret-secret'));

    expect($events->published[array_key_last($events->published)]->to->value())->toBe('ada.new@shoppy.test');

    $confirm->handle(new ConfirmEmailChangeCommand(tokenFromLastEmail($events)));

    expect($users->findById($userId)?->email()->value())->toBe('ada.new@shoppy.test')
        ->and($users->findById($userId)?->isEmailVerified())->toBeTrue()
        ->and($accessTokens->findByHash(TokenHash::fromHash(hash('sha256', 'test-access-token'))))->toBeNull();
});

it('revokes every session on password change and on logout all', function () {
    [, , $accessTokens, , , $register, , , , $changePassword, , , , $logoutAll, $login] = accountContext();
    $userId = $register->handle(new RegisterAccountCommand('ada@shoppy.test', 'secret-secret'));
    $login->handle(new LoginCommand('ada@shoppy.test', 'secret-secret'));

    $logoutAll->handle(new LogoutAllCommand($userId->value()));
    expect($accessTokens->findByHash(TokenHash::fromHash(hash('sha256', 'test-access-token'))))->toBeNull();

    $login->handle(new LoginCommand('ada@shoppy.test', 'secret-secret'));
    $changePassword->handle(new ChangePasswordCommand($userId->value(), 'secret-secret', 'brand-new-secret'));

    expect($accessTokens->findByHash(TokenHash::fromHash(hash('sha256', 'test-access-token'))))->toBeNull();
});

it('anonymizes an account and refuses to delete the last admin', function () {
    [$users, $credentials, , , , $register, , , , , , , $delete] = accountContext();
    $userId = $register->handle(new RegisterAccountCommand('ada@shoppy.test', 'secret-secret'));
    $user = $users->findById($userId);
    $user->assignRole(Role::admin());
    $users->save($user);

    expect(fn () => $delete->handle(new DeleteAccountCommand($userId->value(), 'secret-secret')))
        ->toThrow(LastAdminAccount::class);

    $user->assignRole(Role::customer());
    $users->save($user);
    $delete->handle(new DeleteAccountCommand($userId->value(), 'secret-secret'));

    expect($users->findById($userId)?->isDeleted())->toBeTrue()
        ->and($users->findById($userId)?->email()->value())->toBe('deleted.'.str_replace('-', '', $userId->value()).'@users.invalid')
        ->and($credentials->findByUserId($userId))->toBeNull();
});
