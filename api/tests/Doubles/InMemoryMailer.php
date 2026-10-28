<?php

declare(strict_types=1);

namespace App\Tests\Doubles;

use App\Email\Application\Port\Mailer;
use App\Email\Domain\OutboundEmail;

final class InMemoryMailer implements Mailer
{
    /** @var list<OutboundEmail> */
    public array $sent = [];

    /** @var list<OutboundEmail> */
    private static array $mailbox = [];

    public function send(OutboundEmail $email): void
    {
        $this->sent[] = $email;
        self::$mailbox[] = $email;
    }

    public function clear(): void
    {
        $this->sent = [];
        self::$mailbox = [];
    }

    /**
     * @return list<OutboundEmail>
     */
    public static function mailbox(): array
    {
        return self::$mailbox;
    }

    public static function resetMailbox(): void
    {
        self::$mailbox = [];
    }
}
