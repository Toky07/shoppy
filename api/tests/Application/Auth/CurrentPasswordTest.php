<?php

declare(strict_types=1);

use App\Auth\Application\CurrentPassword;
use App\Auth\Domain\Entity\Credentials;
use App\Auth\Domain\ValueObject\PlainPassword;
use App\Auth\Infrastructure\Persistence\InMemoryCredentialsRepository;
use App\Tests\Doubles\FakePasswordHasher;
use App\User\Domain\ValueObject\UserId;

it('accepts a stored password that is shorter than the current policy', function () {
    $credentials = new InMemoryCredentialsRepository();
    $hasher = new FakePasswordHasher();
    $userId = UserId::fromString('11111111-1111-4111-8111-111111111111');
    $credentials->save(Credentials::create(
        $userId,
        $hasher->hash(PlainPassword::forVerification('12345678')),
    ));

    $verified = (new CurrentPassword($credentials, $hasher))->require($userId, '12345678');

    expect($verified->userId())->toEqual($userId);
});
