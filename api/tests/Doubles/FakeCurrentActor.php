<?php

declare(strict_types=1);

namespace App\Tests\Doubles;

use App\Audit\Application\Port\CurrentActor;

final readonly class FakeCurrentActor implements CurrentActor
{
    public function __construct(
        private ?string $id = 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaa9',
        private ?string $email = 'admin@nuvora.test',
    ) {
    }

    public function id(): ?string
    {
        return $this->id;
    }

    public function email(): ?string
    {
        return $this->email;
    }
}
