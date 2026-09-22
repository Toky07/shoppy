<?php

declare(strict_types=1);

namespace App\Email\Application\CommandHandler;

use App\Email\Application\Command\SendEmailCommand;
use App\Email\Application\Port\Mailer;
use App\Email\Domain\OutboundEmail;

final readonly class SendEmailCommandHandler
{
    public function __construct(private Mailer $mailer)
    {
    }

    public function handle(SendEmailCommand $command): void
    {
        $this->mailer->send(OutboundEmail::compose(
            $command->to,
            $command->subject,
            $command->body,
            $command->attachments,
        ));
    }
}
