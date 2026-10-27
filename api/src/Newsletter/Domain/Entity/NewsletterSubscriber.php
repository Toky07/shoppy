<?php

declare(strict_types=1);

namespace App\Newsletter\Domain\Entity;

use App\Email\Domain\ValueObject\EmailAddress;
use DateTimeImmutable;

final readonly class NewsletterSubscriber
{
    private function __construct(
        private EmailAddress $email,
        private DateTimeImmutable $subscribedAt,
    ) {
    }

    public static function subscribe(EmailAddress $email, DateTimeImmutable $subscribedAt): self
    {
        return new self($email, $subscribedAt);
    }

    public function email(): EmailAddress
    {
        return $this->email;
    }

    public function subscribedAt(): DateTimeImmutable
    {
        return $this->subscribedAt;
    }
}
