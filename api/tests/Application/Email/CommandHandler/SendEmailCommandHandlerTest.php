<?php

declare(strict_types=1);

use App\Email\Application\Command\SendEmailCommand;
use App\Email\Application\CommandHandler\SendEmailCommandHandler;
use App\Email\Domain\Exception\EmailDeliveryFailed;
use App\Email\Domain\OutboundEmail;
use App\Email\Domain\ValueObject\EmailAddress;
use App\Email\Domain\ValueObject\EmailAttachment;
use App\Email\Domain\ValueObject\EmailBody;
use App\Email\Domain\ValueObject\EmailSubject;
use App\Email\Application\Port\Mailer;
use App\Tests\Doubles\InMemoryMailer;

it('sends the composed email through the mailer port', function () {
    $mailer = new InMemoryMailer();
    $to = EmailAddress::fromString('ada@shoppy.test');
    $subject = EmailSubject::fromString('Bienvenue');
    $body = EmailBody::fromText('Bonjour Ada', '<p>Bonjour Ada</p>');

    (new SendEmailCommandHandler($mailer))->handle(new SendEmailCommand($to, $subject, $body));

    expect($mailer->sent)->toHaveCount(1)
        ->and($mailer->sent[0]->to())->toBe($to)
        ->and($mailer->sent[0]->subject())->toBe($subject)
        ->and($mailer->sent[0]->body()->text())->toBe('Bonjour Ada')
        ->and($mailer->sent[0]->body()->html())->toBe('<p>Bonjour Ada</p>')
        ->and($mailer->sent[0]->attachments())->toBe([]);
});

it('sends attachments with the email', function () {
    $mailer = new InMemoryMailer();
    $receipt = EmailAttachment::fromContent('recu.txt', 'text/plain', 'Total : 39,98 €');

    (new SendEmailCommandHandler($mailer))->handle(new SendEmailCommand(
        EmailAddress::fromString('ada@shoppy.test'),
        EmailSubject::fromString('Commande confirmée'),
        EmailBody::fromText('Votre commande est enregistrée.'),
        [$receipt],
    ));

    expect($mailer->sent[0]->attachments())->toBe([$receipt]);
});

it('propagates a delivery failure from the mailer port', function () {
    $mailer = new class implements Mailer {
        public function send(OutboundEmail $email): void
        {
            throw new EmailDeliveryFailed();
        }
    };

    (new SendEmailCommandHandler($mailer))->handle(new SendEmailCommand(
        EmailAddress::fromString('ada@shoppy.test'),
        EmailSubject::fromString('Bienvenue'),
        EmailBody::fromText('Bonjour Ada'),
    ));
})->throws(EmailDeliveryFailed::class);
