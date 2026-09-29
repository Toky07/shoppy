<?php

declare(strict_types=1);

use App\Auth\Infrastructure\Security\AuthenticatedUser;
use App\User\Domain\ValueObject\Role;

it('maps domain admin role to Symfony roles', function () {
    $user = AuthenticatedUser::fromDomainRole('admin-id', Role::admin(), 'token');

    expect($user->isAdmin())->toBeTrue()
        ->and($user->domainRole()->value())->toBe(Role::admin()->value())
        ->and($user->getRoles())->toBe([AuthenticatedUser::ROLE_ADMIN, AuthenticatedUser::ROLE_USER]);
});

it('maps domain customer role to Symfony user role only', function () {
    $user = AuthenticatedUser::fromDomainRole('customer-id', Role::customer(), 'token');

    expect($user->isAdmin())->toBeFalse()
        ->and($user->domainRole()->value())->toBe(Role::customer()->value())
        ->and($user->getRoles())->toBe([AuthenticatedUser::ROLE_USER]);
});
