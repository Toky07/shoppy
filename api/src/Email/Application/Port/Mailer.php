<?php

declare(strict_types=1);

namespace App\Email\Application\Port;

use App\Email\Domain\OutboundEmail;

interface Mailer
{
    public function send(OutboundEmail $email): void;
}
