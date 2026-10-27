<?php

declare(strict_types=1);

namespace App\Audit\Application\Port;

interface CurrentActor
{
    public function id(): ?string;

    public function email(): ?string;
}
