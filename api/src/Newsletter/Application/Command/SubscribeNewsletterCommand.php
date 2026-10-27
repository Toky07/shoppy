<?php

declare(strict_types=1);

namespace App\Newsletter\Application\Command;

final readonly class SubscribeNewsletterCommand
{
    public function __construct(public string $email)
    {
    }
}
