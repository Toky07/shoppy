<?php

declare(strict_types=1);

namespace App\Media\Application\Port;

interface MediaOwnerVisibility
{
    public function isPubliclyListable(string $ownerType, string $ownerId): bool;
}
