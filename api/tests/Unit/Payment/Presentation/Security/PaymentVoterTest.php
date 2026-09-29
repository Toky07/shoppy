<?php

declare(strict_types=1);

use App\Auth\Infrastructure\Security\AuthenticatedUser;
use App\Payment\Presentation\Security\PaymentAccessSubject;
use App\Payment\Presentation\Security\PaymentVoter;
use App\User\Domain\ValueObject\Role;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Http\Authenticator\Token\PostAuthenticationToken;

function paymentVoterToken(AuthenticatedUser $user): TokenInterface
{
    return new PostAuthenticationToken($user, 'main', $user->getRoles());
}

it('allows the paying customer to access their payment', function () {
    $voter = new PaymentVoter();
    $customerId = '11111111-1111-4111-8111-111111111111';
    $token = paymentVoterToken(AuthenticatedUser::fromDomainRole($customerId, Role::customer(), 'token'));

    expect($voter->vote($token, new PaymentAccessSubject($customerId), [PaymentVoter::ACCESS]))
        ->toBe(VoterInterface::ACCESS_GRANTED);
});

it('denies another customer payment access', function () {
    $voter = new PaymentVoter();
    $token = paymentVoterToken(AuthenticatedUser::fromDomainRole(
        '22222222-2222-4222-8222-222222222222',
        Role::customer(),
        'token',
    ));

    expect($voter->vote($token, new PaymentAccessSubject('11111111-1111-4111-8111-111111111111'), [PaymentVoter::ACCESS]))
        ->toBe(VoterInterface::ACCESS_DENIED);
});

it('denies an admin payment access for another customer', function () {
    $voter = new PaymentVoter();
    $token = paymentVoterToken(AuthenticatedUser::fromDomainRole('admin-id', Role::admin(), 'token'));

    expect($voter->vote($token, new PaymentAccessSubject('11111111-1111-4111-8111-111111111111'), [PaymentVoter::ACCESS]))
        ->toBe(VoterInterface::ACCESS_DENIED);
});
