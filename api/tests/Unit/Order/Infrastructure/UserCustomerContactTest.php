<?php

declare(strict_types=1);

use App\Order\Domain\ValueObject\CustomerId;
use App\Order\Infrastructure\Customer\UserCustomerContact;
use App\Payment\Domain\ValueObject\CustomerReference;
use App\Payment\Infrastructure\Customer\UserPayerContact;
use App\User\Domain\Entity\User;
use App\User\Domain\ValueObject\Email;
use App\User\Domain\ValueObject\UserId;
use App\User\Infrastructure\Persistence\InMemoryUserRepository;

it('reads the customer address from the user directory', function () {
    $users = new InMemoryUserRepository();
    $users->save(User::register(
        UserId::fromString('11111111-1111-4111-8111-111111111111'),
        Email::fromString('ada@shoppy.test'),
        new DateTimeImmutable('2026-08-20T12:00:00+00:00'),
    ));

    $address = (new UserCustomerContact($users))->emailFor(
        CustomerId::fromString('11111111-1111-4111-8111-111111111111'),
    );

    expect($address)->toBe('ada@shoppy.test')
        ->and((new UserPayerContact($users))->emailFor(
            CustomerReference::fromString('11111111-1111-4111-8111-111111111111'),
        ))->toBe('ada@shoppy.test');
});

it('returns null when the user does not exist', function () {
    $users = new InMemoryUserRepository();

    expect((new UserCustomerContact($users))->emailFor(
        CustomerId::fromString('11111111-1111-4111-8111-111111111111'),
    ))->toBeNull();
});
