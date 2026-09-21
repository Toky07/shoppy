<?php

declare(strict_types=1);

use App\User\Domain\Entity\User;
use App\User\Domain\Repository\UserRepository;
use App\User\Domain\ValueObject\Email;
use App\User\Domain\ValueObject\Role;
use App\User\Domain\ValueObject\UserId;
use Doctrine\ORM\EntityManagerInterface;

it('persists a user and finds it by email', function () {
    $id = UserId::fromString('11111111-1111-4111-8111-111111111111');
    $createdAt = new DateTimeImmutable('2026-08-20T12:00:00+00:00');
    $user = User::register($id, Email::fromString('ada@nuvora.test'), $createdAt);

    $repository = self::getContainer()->get(UserRepository::class);
    $entityManager = self::getContainer()->get(EntityManagerInterface::class);

    $repository->save($user);
    $entityManager->clear();

    $found = $repository->findByEmail(Email::fromString('ADA@nuvora.test'));

    expect($found)->not->toBeNull()
        ->and($found->id()->value())->toBe($id->value())
        ->and($found->email()->value())->toBe('ada@nuvora.test')
        ->and($found->createdAt()->format(DateTimeInterface::ATOM))->toBe('2026-08-20T12:00:00+00:00')
        ->and($found->role())->toEqual(Role::customer());
});

it('persists an assigned admin role', function () {
    $id = UserId::fromString('11111111-1111-4111-8111-111111111111');
    $user = User::register(
        $id,
        Email::fromString('ada@nuvora.test'),
        new DateTimeImmutable('2026-08-20T12:00:00+00:00'),
    );
    $user->assignRole(Role::admin());

    $repository = self::getContainer()->get(UserRepository::class);
    $entityManager = self::getContainer()->get(EntityManagerInterface::class);

    $repository->save($user);
    $entityManager->clear();

    $found = $repository->findById($id);

    expect($found)->not->toBeNull()
        ->and($found->role())->toEqual(Role::admin());
});

it('updates a persisted user role', function () {
    $id = UserId::fromString('11111111-1111-4111-8111-111111111111');
    $repository = self::getContainer()->get(UserRepository::class);
    $entityManager = self::getContainer()->get(EntityManagerInterface::class);

    $repository->save(User::register(
        $id,
        Email::fromString('ada@nuvora.test'),
        new DateTimeImmutable('2026-08-20T12:00:00+00:00'),
    ));
    $entityManager->clear();

    $found = $repository->findById($id);
    $found->assignRole(Role::admin());
    $repository->save($found);
    $entityManager->clear();

    expect($repository->findById($id)->role())->toEqual(Role::admin());
});

it('returns null when the user does not exist', function () {
    $repository = self::getContainer()->get(UserRepository::class);

    expect($repository->findById(
        UserId::fromString('11111111-1111-4111-8111-111111111111'),
    ))->toBeNull();
});

it('pages users newest first', function () {
    $repository = self::getContainer()->get(UserRepository::class);
    $entityManager = self::getContainer()->get(EntityManagerInterface::class);

    $older = User::register(
        UserId::fromString('11111111-1111-4111-8111-111111111111'),
        Email::fromString('old@nuvora.test'),
        new DateTimeImmutable('2026-08-19T12:00:00+00:00'),
    );
    $newer = User::register(
        UserId::fromString('22222222-2222-4222-8222-222222222222'),
        Email::fromString('new@nuvora.test'),
        new DateTimeImmutable('2026-08-21T12:00:00+00:00'),
        Role::admin(),
    );
    $repository->save($older);
    $repository->save($newer);
    $entityManager->clear();

    $page = $repository->findPage(0, 10);

    expect($repository->countAll())->toBe(2)
        ->and($page[0]->email()->value())->toBe('new@nuvora.test')
        ->and($page[1]->email()->value())->toBe('old@nuvora.test')
        ->and($repository->countAll('new@'))->toBe(1)
        ->and($repository->findPage(0, 10, 'new@')[0]->email()->value())->toBe('new@nuvora.test');
});
