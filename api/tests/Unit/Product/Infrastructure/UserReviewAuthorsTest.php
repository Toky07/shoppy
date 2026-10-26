<?php

declare(strict_types=1);

use App\Product\Infrastructure\Review\UserReviewAuthors;
use App\Tests\Doubles\CountingUserRepository;
use App\User\Domain\Entity\User;
use App\User\Domain\ValueObject\Email;
use App\User\Domain\ValueObject\UserId;
use App\User\Infrastructure\Persistence\InMemoryUserRepository;

function saveAuthor(InMemoryUserRepository $users, string $id, string $email): User
{
    $user = User::register(
        UserId::fromString($id),
        Email::fromString($email),
        new DateTimeImmutable('2026-08-20T12:00:00+00:00'),
    );
    $users->save($user);

    return $user;
}

it('loads review author labels in one user lookup', function () {
    $users = new InMemoryUserRepository();
    saveAuthor($users, '11111111-1111-4111-8111-111111111111', 'ada@nuvora.test');
    saveAuthor($users, '22222222-2222-4222-8222-222222222222', 'grace@nuvora.test');
    $counting = new CountingUserRepository($users);

    $labels = (new UserReviewAuthors($counting))->labelsFor([
        '11111111-1111-4111-8111-111111111111',
        '22222222-2222-4222-8222-222222222222',
        '11111111-1111-4111-8111-111111111111',
        'not-a-uuid',
    ]);

    expect($counting->findByIdsCalls)->toBe(1)
        ->and($counting->findByIdCalls)->toBe(0)
        ->and($labels)->toBe([
            '11111111-1111-4111-8111-111111111111' => 'ada',
            '22222222-2222-4222-8222-222222222222' => 'grace',
            'not-a-uuid' => 'Client',
        ]);
});

it('hides deleted authors', function () {
    $users = new InMemoryUserRepository();
    $author = saveAuthor($users, '11111111-1111-4111-8111-111111111111', 'ada@nuvora.test');
    $author->anonymize(new DateTimeImmutable('2026-09-22T12:00:00+00:00'));
    $users->save($author);

    $labels = (new UserReviewAuthors($users))->labelsFor([
        '11111111-1111-4111-8111-111111111111',
        '22222222-2222-4222-8222-222222222222',
    ]);

    expect($labels)->toBe([
        '11111111-1111-4111-8111-111111111111' => 'Client',
        '22222222-2222-4222-8222-222222222222' => 'Client',
    ]);
});
