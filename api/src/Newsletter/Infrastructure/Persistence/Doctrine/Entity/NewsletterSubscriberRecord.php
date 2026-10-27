<?php

declare(strict_types=1);

namespace App\Newsletter\Infrastructure\Persistence\Doctrine\Entity;

use App\Email\Domain\ValueObject\EmailAddress;
use App\Newsletter\Domain\Entity\NewsletterSubscriber;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'newsletter_subscribers')]
class NewsletterSubscriberRecord
{
    #[ORM\Id]
    #[ORM\Column(length: 255)]
    private string $email;

    #[ORM\Column(name: 'subscribed_at')]
    private DateTimeImmutable $subscribedAt;

    public static function fromDomain(NewsletterSubscriber $subscriber): self
    {
        $record = new self();
        $record->email = $subscriber->email()->value();
        $record->subscribedAt = $subscriber->subscribedAt();

        return $record;
    }

    public function toDomain(): NewsletterSubscriber
    {
        return NewsletterSubscriber::subscribe(
            EmailAddress::fromString($this->email),
            $this->subscribedAt,
        );
    }
}
