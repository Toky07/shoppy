<?php

declare(strict_types=1);

use App\User\Domain\Exception\InvalidUserRole;
use App\User\Domain\ValueObject\Role;

it('exposes the customer role', function () {
    expect(Role::customer()->value())->toBe('customer');
});

it('exposes the admin role', function () {
    expect(Role::admin()->value())->toBe('admin');
});

it('creates a role from a valid value', function () {
    expect(Role::fromString('admin'))->toEqual(Role::admin());
});

it('rejects an unknown role', function () {
    Role::fromString('superuser');
})->throws(InvalidUserRole::class);
