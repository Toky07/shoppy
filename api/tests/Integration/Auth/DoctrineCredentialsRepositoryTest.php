<?php

declare(strict_types=1);

use App\Auth\Domain\Entity\Credentials;
use App\Auth\Domain\Repository\CredentialsRepository;
use App\Auth\Domain\ValueObject\PlainPassword;
use App\Auth\Infrastructure\Security\NativePasswordHasher;
use App\User\Domain\Entity\User;
use App\User\Domain\Repository\UserRepository;
use App\User\Domain\ValueObject\Email;
use App\User\Domain\ValueObject\UserId;
use Doctrine\ORM\EntityManagerInterface;

it('persists hashed credentials for a user', function () {
    $userId = UserId::fromString('11111111-1111-4111-8111-111111111111');
    $users = self::getContainer()->get(UserRepository::class);
    $credentials = self::getContainer()->get(CredentialsRepository::class);
    $entityManager = self::getContainer()->get(EntityManagerInterface::class);

    $users->save(User::register(
        $userId,
        Email::fromString('ada@nuvora.test'),
        new DateTimeImmutable('2026-08-20T12:00:00+00:00'),
    ));

    $hashedPassword = (new NativePasswordHasher())->hash(PlainPassword::fromString('secret-secret'));
    $credentials->save(Credentials::create($userId, $hashedPassword));
    $entityManager->clear();

    $found = $credentials->findByUserId($userId);

    expect($found)->not->toBeNull()
        ->and($found->userId()->value())->toBe($userId->value())
        ->and($found->hashedPassword()->value())->not->toBe('secret-secret')
        ->and(password_verify('secret-secret', $found->hashedPassword()->value()))->toBeTrue();
});
