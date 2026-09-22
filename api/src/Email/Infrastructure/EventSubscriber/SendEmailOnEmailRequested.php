<?php

declare(strict_types=1);

namespace App\Email\Infrastructure\EventSubscriber;

use App\Email\Application\Command\SendEmailCommand;
use App\Email\Application\CommandHandler\SendEmailCommandHandler;
use App\Email\Domain\Event\EmailRequested;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final readonly class SendEmailOnEmailRequested implements EventSubscriberInterface
{
    public function __construct(private SendEmailCommandHandler $sendEmail)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [EmailRequested::class => 'onEmailRequested'];
    }

    public function onEmailRequested(EmailRequested $event): void
    {
        $this->sendEmail->handle(new SendEmailCommand(
            $event->to,
            $event->subject,
            $event->body,
            $event->attachments,
        ));
    }
}
