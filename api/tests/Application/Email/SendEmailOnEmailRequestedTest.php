<?php

declare(strict_types=1);

use App\Email\Application\CommandHandler\SendEmailCommandHandler;
use App\Email\Domain\Event\EmailRequested;
use App\Email\Domain\ValueObject\EmailAddress;
use App\Email\Domain\ValueObject\EmailAttachment;
use App\Email\Domain\ValueObject\EmailBody;
use App\Email\Domain\ValueObject\EmailSubject;
use App\Email\Infrastructure\EventSubscriber\SendEmailOnEmailRequested;
use App\Shared\Infrastructure\Event\SymfonyEventBus;
use App\Tests\Doubles\InMemoryMailer;
use Symfony\Component\EventDispatcher\EventDispatcher;

it('sends an email when another module publishes EmailRequested', function () {
    $mailer = new InMemoryMailer();
    $dispatcher = new EventDispatcher();
    $dispatcher->addSubscriber(new SendEmailOnEmailRequested(new SendEmailCommandHandler($mailer)));
    $bus = new SymfonyEventBus($dispatcher);

    $bus->publish(new EmailRequested(
        EmailAddress::fromString('ada@shoppy.test'),
        EmailSubject::fromString('Commande confirmée'),
        EmailBody::fromText('Votre commande est enregistrée.'),
    ));

    expect($mailer->sent)->toHaveCount(1)
        ->and($mailer->sent[0]->to()->value())->toBe('ada@shoppy.test')
        ->and($mailer->sent[0]->subject()->value())->toBe('Commande confirmée')
        ->and($mailer->sent[0]->body()->text())->toBe('Votre commande est enregistrée.')
        ->and($mailer->sent[0]->attachments())->toBe([]);
});

it('delivers attachments published with the event', function () {
    $mailer = new InMemoryMailer();
    $dispatcher = new EventDispatcher();
    $dispatcher->addSubscriber(new SendEmailOnEmailRequested(new SendEmailCommandHandler($mailer)));
    $receipt = EmailAttachment::fromContent('recu.txt', 'text/plain', 'Total : 39,98 €');

    (new SymfonyEventBus($dispatcher))->publish(new EmailRequested(
        EmailAddress::fromString('ada@shoppy.test'),
        EmailSubject::fromString('Commande confirmée'),
        EmailBody::fromText('Votre commande est enregistrée.'),
        [$receipt],
    ));

    expect($mailer->sent[0]->attachments())->toBe([$receipt]);
});
