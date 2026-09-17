<?php

declare(strict_types=1);

namespace App\Media\Application\Command;

final readonly class DeleteMediaCommand
{
    public function __construct(public string $id)
    {
    }
}
