<?php

declare(strict_types=1);

namespace App\Product\Infrastructure\Review;

use App\Product\Application\Port\ReviewAuthors;
use App\User\Domain\Entity\User;
use App\User\Domain\Exception\InvalidUserId;
use App\User\Domain\Repository\UserRepository;
use App\User\Domain\ValueObject\UserId;

final readonly class UserReviewAuthors implements ReviewAuthors
{
    public function __construct(private UserRepository $users)
    {
    }

    public function labelsFor(array $authorIds): array
    {
        $ids = [];

        foreach (array_values(array_unique($authorIds)) as $authorId) {
            try {
                $ids[] = UserId::fromString($authorId);
            } catch (InvalidUserId) {
            }
        }

        $found = [];

        foreach ($this->users->findByIds($ids) as $user) {
            $found[$user->id()->value()] = $user;
        }

        $labels = [];

        foreach ($authorIds as $authorId) {
            $labels[$authorId] = $this->label($found[$authorId] ?? null);
        }

        return $labels;
    }

    private function label(?User $user): string
    {
        if ($user === null || $user->isDeleted()) {
            return 'Client';
        }

        $localPart = strstr($user->email()->value(), '@', true);

        return $localPart === false || $localPart === '' ? 'Client' : $localPart;
    }
}
