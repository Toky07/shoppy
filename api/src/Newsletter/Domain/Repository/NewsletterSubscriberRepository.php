<?php

declare(strict_types=1);

namespace App\Newsletter\Domain\Repository;

use App\Email\Domain\ValueObject\EmailAddress;
use App\Newsletter\Domain\Entity\NewsletterSubscriber;

interface NewsletterSubscriberRepository
{
    public function findByEmail(EmailAddress $email): ?NewsletterSubscriber;

    public function save(NewsletterSubscriber $subscriber): void;
}
