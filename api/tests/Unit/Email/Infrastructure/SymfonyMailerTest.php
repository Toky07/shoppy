<?php

declare(strict_types=1);

use App\Email\Domain\Exception\EmailDeliveryFailed;
use App\Email\Domain\Exception\InvalidEmailAddress;
use App\Email\Domain\OutboundEmail;
use App\Email\Domain\ValueObject\EmailAddress;
use App\Email\Domain\ValueObject\EmailAttachment;
use App\Email\Domain\ValueObject\EmailBody;
use App\Email\Domain\ValueObject\EmailSubject;
use App\Email\Infrastructure\Mailer\SymfonyMailer;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\RawMessage;

it('maps an outbound email onto a symfony message', function () {
    $messages = [];
    $transport = new class($messages) implements MailerInterface {
        /** @param list<RawMessage> $messages */
        public function __construct(private array &$messages)
        {
        }

        public function send(RawMessage $message, ?Envelope $envelope = null): void
        {
            $this->messages[] = $message;
        }
    };

    (new SymfonyMailer($transport, 'noreply@shoppy.test'))->send(OutboundEmail::compose(
        EmailAddress::fromString('ada@shoppy.test'),
        EmailSubject::fromString('Bienvenue'),
        EmailBody::fromText('Bonjour Ada', '<p>Bonjour Ada</p>'),
    ));

    expect($messages)->toHaveCount(1)
        ->and($messages[0])->toBeInstanceOf(Email::class)
        ->and($messages[0]->getFrom()[0]->getAddress())->toBe('noreply@shoppy.test')
        ->and($messages[0]->getTo()[0]->getAddress())->toBe('ada@shoppy.test')
        ->and($messages[0]->getSubject())->toBe('Bienvenue')
        ->and($messages[0]->getTextBody())->toBe('Bonjour Ada')
        ->and($messages[0]->getHtmlBody())->toBe('<p>Bonjour Ada</p>')
        ->and($messages[0]->getAttachments())->toBe([]);
});

it('attaches files to the symfony message', function () {
    $messages = [];
    $transport = new class($messages) implements MailerInterface {
        /** @param list<RawMessage> $messages */
        public function __construct(private array &$messages)
        {
        }

        public function send(RawMessage $message, ?Envelope $envelope = null): void
        {
            $this->messages[] = $message;
        }
    };

    (new SymfonyMailer($transport, 'noreply@shoppy.test'))->send(OutboundEmail::compose(
        EmailAddress::fromString('ada@shoppy.test'),
        EmailSubject::fromString('Commande confirmée'),
        EmailBody::fromText('Votre commande est enregistrée.'),
        [EmailAttachment::fromContent('recu.txt', 'text/plain', 'Total : 39,98 €')],
    ));

    expect($messages[0]->getAttachments())->toHaveCount(1)
        ->and($messages[0]->getAttachments()[0]->getFilename())->toBe('recu.txt')
        ->and($messages[0]->getAttachments()[0]->getContentType())->toBe('text/plain')
        ->and($messages[0]->getAttachments()[0]->getBody())->toBe('Total : 39,98 €');
});

it('rejects an invalid sender address', function () {
    $transport = new class implements MailerInterface {
        public function send(RawMessage $message, ?Envelope $envelope = null): void
        {
        }
    };

    new SymfonyMailer($transport, 'not-an-email');
})->throws(InvalidEmailAddress::class);

it('translates a transport failure into a domain failure', function () {
    $transport = new class implements MailerInterface {
        public function send(RawMessage $message, ?Envelope $envelope = null): void
        {
            throw new TransportException('smtp down');
        }
    };

    (new SymfonyMailer($transport, 'noreply@shoppy.test'))->send(OutboundEmail::compose(
        EmailAddress::fromString('ada@shoppy.test'),
        EmailSubject::fromString('Bienvenue'),
        EmailBody::fromText('Bonjour Ada'),
    ));
})->throws(EmailDeliveryFailed::class);
