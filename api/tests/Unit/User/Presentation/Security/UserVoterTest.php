<?php

declare(strict_types=1);

use App\Auth\Infrastructure\Security\AuthenticatedUser;
use App\User\Domain\ValueObject\Role;
use App\User\Presentation\Security\UserAccessSubject;
use App\User\Presentation\Security\UserVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Http\Authenticator\Token\PostAuthenticationToken;

function userVoterToken(AuthenticatedUser $user): TokenInterface
{
    return new PostAuthenticationToken($user, 'main', $user->getRoles());
}

it('allows a user to view themselves', function () {
    $voter = new UserVoter();
    $userId = '11111111-1111-4111-8111-111111111111';
    $token = userVoterToken(AuthenticatedUser::fromDomainRole($userId, Role::customer(), 'token'));

    expect($voter->vote($token, new UserAccessSubject($userId), [UserVoter::VIEW]))
        ->toBe(VoterInterface::ACCESS_GRANTED);
});

it('allows an admin to view another user', function () {
    $voter = new UserVoter();
    $token = userVoterToken(AuthenticatedUser::fromDomainRole('admin-id', Role::admin(), 'token'));

    expect($voter->vote($token, new UserAccessSubject('11111111-1111-4111-8111-111111111111'), [UserVoter::VIEW]))
        ->toBe(VoterInterface::ACCESS_GRANTED);
});

it('denies a customer viewing another user', function () {
    $voter = new UserVoter();
    $token = userVoterToken(AuthenticatedUser::fromDomainRole(
        '11111111-1111-4111-8111-111111111111',
        Role::customer(),
        'token',
    ));

    expect($voter->vote($token, new UserAccessSubject('22222222-2222-4222-8222-222222222222'), [UserVoter::VIEW]))
        ->toBe(VoterInterface::ACCESS_DENIED);
});
