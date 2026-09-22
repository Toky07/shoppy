<?php

declare(strict_types=1);

namespace App\Product\Infrastructure\Review;

use App\Product\Application\Port\ReviewAuthors;
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
        $labels = [];

        foreach ($authorIds as $authorId) {
            $labels[$authorId] = $this->label($authorId);
        }

        return $labels;
    }

    private function label(string $authorId): string
    {
        try {
            $user = $this->users->findById(UserId::fromString($authorId));
        } catch (InvalidUserId) {
            return 'Client';
        }

        if ($user === null || $user->isDeleted()) {
            return 'Client';
        }

        $localPart = strstr($user->email()->value(), '@', true);

        return $localPart === false || $localPart === '' ? 'Client' : $localPart;
    }
}
