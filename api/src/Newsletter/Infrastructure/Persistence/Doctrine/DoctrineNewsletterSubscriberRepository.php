<?php

declare(strict_types=1);

namespace App\Newsletter\Infrastructure\Persistence\Doctrine;

use App\Email\Domain\ValueObject\EmailAddress;
use App\Newsletter\Domain\Entity\NewsletterSubscriber;
use App\Newsletter\Domain\Repository\NewsletterSubscriberRepository;
use App\Newsletter\Infrastructure\Persistence\Doctrine\Entity\NewsletterSubscriberRecord;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrineNewsletterSubscriberRepository implements NewsletterSubscriberRepository
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function findByEmail(EmailAddress $email): ?NewsletterSubscriber
    {
        $record = $this->entityManager->find(NewsletterSubscriberRecord::class, $email->value());

        return $record instanceof NewsletterSubscriberRecord ? $record->toDomain() : null;
    }

    public function save(NewsletterSubscriber $subscriber): void
    {
        $this->entityManager->persist(NewsletterSubscriberRecord::fromDomain($subscriber));
        $this->entityManager->flush();
    }
}
