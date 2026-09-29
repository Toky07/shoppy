<?php

declare(strict_types=1);

use App\Auth\Infrastructure\Security\AuthenticatedUser;
use App\Order\Presentation\Security\OrderAccessSubject;
use App\Order\Presentation\Security\OrderVoter;
use App\User\Domain\ValueObject\Role;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Http\Authenticator\Token\PostAuthenticationToken;

function orderVoterToken(AuthenticatedUser $user): TokenInterface
{
    return new PostAuthenticationToken($user, 'main', $user->getRoles());
}

it('allows an admin to view any order', function () {
    $voter = new OrderVoter();
    $token = orderVoterToken(AuthenticatedUser::fromDomainRole(
        'admin-id',
        Role::admin(),
        'token',
    ));

    expect($voter->vote($token, new OrderAccessSubject('other-customer'), [OrderVoter::VIEW]))
        ->toBe(VoterInterface::ACCESS_GRANTED);
});

it('allows a customer to view their own order', function () {
    $voter = new OrderVoter();
    $customerId = '11111111-1111-4111-8111-111111111111';
    $token = orderVoterToken(AuthenticatedUser::fromDomainRole(
        $customerId,
        Role::customer(),
        'token',
    ));

    expect($voter->vote($token, new OrderAccessSubject($customerId), [OrderVoter::VIEW]))
        ->toBe(VoterInterface::ACCESS_GRANTED);
});

it('denies a customer viewing another users order', function () {
    $voter = new OrderVoter();
    $token = orderVoterToken(AuthenticatedUser::fromDomainRole(
        '11111111-1111-4111-8111-111111111111',
        Role::customer(),
        'token',
    ));

    expect($voter->vote($token, new OrderAccessSubject('22222222-2222-4222-8222-222222222222'), [OrderVoter::VIEW]))
        ->toBe(VoterInterface::ACCESS_DENIED);
});

it('allows a customer to cancel their own order', function () {
    $voter = new OrderVoter();
    $customerId = '11111111-1111-4111-8111-111111111111';
    $token = orderVoterToken(AuthenticatedUser::fromDomainRole(
        $customerId,
        Role::customer(),
        'token',
    ));

    expect($voter->vote($token, new OrderAccessSubject($customerId), [OrderVoter::CANCEL]))
        ->toBe(VoterInterface::ACCESS_GRANTED);
});

it('denies an admin cancelling another customers order', function () {
    $voter = new OrderVoter();
    $token = orderVoterToken(AuthenticatedUser::fromDomainRole(
        'admin-id',
        Role::admin(),
        'token',
    ));

    expect($voter->vote($token, new OrderAccessSubject('11111111-1111-4111-8111-111111111111'), [OrderVoter::CANCEL]))
        ->toBe(VoterInterface::ACCESS_DENIED);
});
