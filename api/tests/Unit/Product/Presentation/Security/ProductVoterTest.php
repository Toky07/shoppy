<?php

declare(strict_types=1);

use App\Auth\Infrastructure\Security\AuthenticatedUser;
use App\Product\Presentation\Security\ProductVoter;
use App\User\Domain\ValueObject\Role;
use Symfony\Component\Security\Core\Authentication\Token\NullToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Http\Authenticator\Token\PostAuthenticationToken;

function productVoterToken(AuthenticatedUser $user): TokenInterface
{
    return new PostAuthenticationToken($user, 'main', $user->getRoles());
}

it('grants catalog admin actions to an admin', function () {
    $voter = new ProductVoter();
    $token = productVoterToken(AuthenticatedUser::fromDomainRole('admin-id', Role::admin(), 'token'));

    expect($voter->vote($token, null, [ProductVoter::ADMIN]))
        ->toBe(VoterInterface::ACCESS_GRANTED);
});

it('denies catalog admin actions to a customer', function () {
    $voter = new ProductVoter();
    $token = productVoterToken(AuthenticatedUser::fromDomainRole(
        '11111111-1111-4111-8111-111111111111',
        Role::customer(),
        'token',
    ));

    expect($voter->vote($token, null, [ProductVoter::ADMIN]))
        ->toBe(VoterInterface::ACCESS_DENIED);
});

it('denies catalog admin actions to anonymous users', function () {
    $voter = new ProductVoter();

    expect($voter->vote(new NullToken(), null, [ProductVoter::ADMIN]))
        ->toBe(VoterInterface::ACCESS_DENIED);
});
