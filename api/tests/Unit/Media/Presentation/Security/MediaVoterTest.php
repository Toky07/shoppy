<?php

declare(strict_types=1);

use App\Auth\Infrastructure\Security\AuthenticatedUser;
use App\Media\Domain\ValueObject\MediaOwnerType;
use App\Media\Presentation\Security\MediaListSubject;
use App\Media\Presentation\Security\MediaVoter;
use App\User\Domain\ValueObject\Role;
use Symfony\Component\Security\Core\Authentication\Token\NullToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Http\Authenticator\Token\PostAuthenticationToken;

function mediaVoterToken(AuthenticatedUser $user): TokenInterface
{
    return new PostAuthenticationToken($user, 'main', $user->getRoles());
}

it('allows listing product media without authentication', function () {
    $voter = new MediaVoter();

    expect($voter->vote(
        new NullToken(),
        new MediaListSubject(MediaOwnerType::PRODUCT_ALIAS),
        [MediaVoter::LIST],
    ))->toBe(VoterInterface::ACCESS_GRANTED);
});

it('allows an admin to list non-product media', function () {
    $voter = new MediaVoter();
    $token = mediaVoterToken(AuthenticatedUser::fromDomainRole('admin-id', Role::admin(), 'token'));

    expect($voter->vote($token, new MediaListSubject('category'), [MediaVoter::LIST]))
        ->toBe(VoterInterface::ACCESS_GRANTED);
});

it('denies a customer listing non-product media', function () {
    $voter = new MediaVoter();
    $token = mediaVoterToken(AuthenticatedUser::fromDomainRole(
        '11111111-1111-4111-8111-111111111111',
        Role::customer(),
        'token',
    ));

    expect($voter->vote($token, new MediaListSubject('category'), [MediaVoter::LIST]))
        ->toBe(VoterInterface::ACCESS_DENIED);
});
