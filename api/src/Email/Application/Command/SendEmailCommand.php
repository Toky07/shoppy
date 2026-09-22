<?php

declare(strict_types=1);

namespace App\Email\Application\Command;

use App\Email\Domain\ValueObject\EmailAddress;
use App\Email\Domain\ValueObject\EmailAttachment;
use App\Email\Domain\ValueObject\EmailBody;
use App\Email\Domain\ValueObject\EmailSubject;

final readonly class SendEmailCommand
{
    /**
     * @param list<EmailAttachment> $attachments
     */
    public function __construct(
        public EmailAddress $to,
        public EmailSubject $subject,
        public EmailBody $body,
        public array $attachments = [],
    ) {
    }
}
