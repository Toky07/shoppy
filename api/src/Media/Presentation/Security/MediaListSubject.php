<?php

declare(strict_types=1);

namespace App\Media\Presentation\Security;

final readonly class MediaListSubject
{
    public function __construct(public string $ownerTypeAlias)
    {
    }
}
