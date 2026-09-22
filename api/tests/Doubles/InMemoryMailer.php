<?php

declare(strict_types=1);

namespace App\Tests\Doubles;

use App\Email\Application\Port\Mailer;
use App\Email\Domain\OutboundEmail;

final class InMemoryMailer implements Mailer
{
    /** @var list<OutboundEmail> */
    public array $sent = [];

    public function send(OutboundEmail $email): void
    {
        $this->sent[] = $email;
    }
}
