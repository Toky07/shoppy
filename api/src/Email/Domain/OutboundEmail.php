<?php

declare(strict_types=1);

namespace App\Email\Domain;

use App\Email\Domain\Exception\InvalidEmailAttachment;
use App\Email\Domain\ValueObject\EmailAddress;
use App\Email\Domain\ValueObject\EmailAttachment;
use App\Email\Domain\ValueObject\EmailBody;
use App\Email\Domain\ValueObject\EmailSubject;

final readonly class OutboundEmail
{
    /**
     * @param list<EmailAttachment> $attachments
     */
    private function __construct(
        private EmailAddress $to,
        private EmailSubject $subject,
        private EmailBody $body,
        private array $attachments,
    ) {
    }

    /**
     * @param list<EmailAttachment> $attachments
     */
    public static function compose(
        EmailAddress $to,
        EmailSubject $subject,
        EmailBody $body,
        array $attachments = [],
    ): self {
        foreach ($attachments as $attachment) {
            if (!$attachment instanceof EmailAttachment) {
                throw new InvalidEmailAttachment();
            }
        }

        return new self($to, $subject, $body, array_values($attachments));
    }

    public function to(): EmailAddress
    {
        return $this->to;
    }

    public function subject(): EmailSubject
    {
        return $this->subject;
    }

    public function body(): EmailBody
    {
        return $this->body;
    }

    /**
     * @return list<EmailAttachment>
     */
    public function attachments(): array
    {
        return $this->attachments;
    }
}
