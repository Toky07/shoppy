<?php

declare(strict_types=1);

namespace App\Email\Infrastructure\Mailer;

use App\Email\Application\Port\Mailer;
use App\Email\Domain\Exception\EmailDeliveryFailed;
use App\Email\Domain\OutboundEmail;
use App\Email\Domain\ValueObject\EmailAddress;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

final readonly class SymfonyMailer implements Mailer
{
    private EmailAddress $from;

    public function __construct(
        private MailerInterface $mailer,
        string $fromAddress,
    ) {
        $this->from = EmailAddress::fromString($fromAddress);
    }

    public function send(OutboundEmail $email): void
    {
        $message = (new Email())
            ->from($this->from->value())
            ->to($email->to()->value())
            ->subject($email->subject()->value())
            ->text($email->body()->text());

        if ($email->body()->html() !== null) {
            $message->html($email->body()->html());
        }

        foreach ($email->attachments() as $attachment) {
            $message->attach($attachment->content(), $attachment->filename(), $attachment->mimeType());
        }

        try {
            $this->mailer->send($message);
        } catch (TransportExceptionInterface $exception) {
            throw new EmailDeliveryFailed($exception);
        }
    }
}
