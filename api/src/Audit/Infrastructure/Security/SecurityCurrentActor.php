<?php

declare(strict_types=1);

namespace App\Audit\Infrastructure\Security;

use App\Audit\Application\Port\CurrentActor;
use App\Auth\Presentation\Http\CurrentUser;
use App\User\Domain\Repository\UserRepository;
use App\User\Domain\ValueObject\UserId;

final readonly class SecurityCurrentActor implements CurrentActor
{
    public function __construct(
        private CurrentUser $currentUser,
        private UserRepository $users,
    ) {
    }

    public function id(): ?string
    {
        return $this->currentUser->idOrNull();
    }

    public function email(): ?string
    {
        $id = $this->id();

        if ($id === null) {
            return null;
        }

        return $this->users->findById(UserId::fromString($id))?->email()->value();
    }
}
