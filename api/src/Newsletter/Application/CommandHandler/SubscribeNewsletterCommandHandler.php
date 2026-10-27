<?php

declare(strict_types=1);

namespace App\Newsletter\Application\CommandHandler;

use App\Email\Domain\Event\EmailRequested;
use App\Email\Domain\Exception\InvalidEmailAddress;
use App\Email\Domain\ValueObject\EmailAddress;
use App\Email\Domain\ValueObject\EmailBody;
use App\Email\Domain\ValueObject\EmailSubject;
use App\Newsletter\Application\Command\SubscribeNewsletterCommand;
use App\Newsletter\Domain\Entity\NewsletterSubscriber;
use App\Newsletter\Domain\Exception\InvalidNewsletterEmail;
use App\Newsletter\Domain\Repository\NewsletterSubscriberRepository;
use App\Shared\Domain\Clock;
use App\Shared\Domain\Event\EventBus;

final readonly class SubscribeNewsletterCommandHandler
{
    public function __construct(
        private NewsletterSubscriberRepository $subscribers,
        private EventBus $events,
        private Clock $clock,
    ) {
    }

    public function handle(SubscribeNewsletterCommand $command): void
    {
        try {
            $email = EmailAddress::fromString($command->email);
        } catch (InvalidEmailAddress) {
            throw new InvalidNewsletterEmail();
        }

        if ($this->subscribers->findByEmail($email) !== null) {
            return;
        }

        $this->subscribers->save(NewsletterSubscriber::subscribe($email, $this->clock->now()));
        $this->events->publish(new EmailRequested(
            $email,
            EmailSubject::fromString('Inscription à la lettre Shoppy'),
            EmailBody::fromText(
                "Vous êtes inscrit à la lettre d'information Shoppy. Écrivez à contact@shoppy.test pour vous désinscrire.",
            ),
        ));
    }
}
