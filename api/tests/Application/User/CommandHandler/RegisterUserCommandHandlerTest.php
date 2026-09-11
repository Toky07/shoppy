<?php

declare(strict_types=1);

use App\User\Application\Command\RegisterUserCommand;
use App\User\Application\CommandHandler\RegisterUserCommandHandler;
use App\User\Domain\Exception\EmailAlreadyRegistered;
use App\User\Domain\ValueObject\Role;
use App\User\Infrastructure\Persistence\InMemoryUserRepository;
use App\Tests\Doubles\FixedClock;

it('registers a user from an email', function () {
    $repository = new InMemoryUserRepository();
    $createdAt = new DateTimeImmutable('2026-08-20T12:00:00+00:00');
    $handler = new RegisterUserCommandHandler($repository, new FixedClock($createdAt));

    $userId = $handler->handle(new RegisterUserCommand(email: 'Ada@Nuvora.test'));

    $user = $repository->findById($userId);

    expect($user)->not->toBeNull()
        ->and($user->email()->value())->toBe('ada@nuvora.test')
        ->and($user->createdAt())->toBe($createdAt)
        ->and($user->role())->toEqual(Role::customer());
});

it('rejects a duplicate email', function () {
    $repository = new InMemoryUserRepository();
    $handler = new RegisterUserCommandHandler(
        $repository,
        new FixedClock(new DateTimeImmutable('2026-08-20T12:00:00+00:00')),
    );

    $handler->handle(new RegisterUserCommand(email: 'ada@nuvora.test'));
    $handler->handle(new RegisterUserCommand(email: 'ADA@nuvora.test'));
})->throws(EmailAlreadyRegistered::class);
