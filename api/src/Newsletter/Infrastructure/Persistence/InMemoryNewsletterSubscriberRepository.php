<?php

declare(strict_types=1);

namespace App\Newsletter\Infrastructure\Persistence;

use App\Email\Domain\ValueObject\EmailAddress;
use App\Newsletter\Domain\Entity\NewsletterSubscriber;
use App\Newsletter\Domain\Repository\NewsletterSubscriberRepository;

final class InMemoryNewsletterSubscriberRepository implements NewsletterSubscriberRepository
{
    /** @var array<string, NewsletterSubscriber> */
    private array $subscribers = [];

    public function findByEmail(EmailAddress $email): ?NewsletterSubscriber
    {
        return $this->subscribers[$email->value()] ?? null;
    }

    public function save(NewsletterSubscriber $subscriber): void
    {
        $this->subscribers[$subscriber->email()->value()] = $subscriber;
    }

    public function count(): int
    {
        return count($this->subscribers);
    }
}
