<?php

declare(strict_types=1);

use App\Email\Domain\Exception\InvalidEmailAttachment;
use App\Email\Domain\OutboundEmail;
use App\Email\Domain\ValueObject\EmailAddress;
use App\Email\Domain\ValueObject\EmailAttachment;
use App\Email\Domain\ValueObject\EmailBody;
use App\Email\Domain\ValueObject\EmailSubject;

it('composes an outbound email', function () {
    $to = EmailAddress::fromString('ada@shoppy.test');
    $subject = EmailSubject::fromString('Bienvenue');
    $body = EmailBody::fromText('Bonjour Ada');

    $email = OutboundEmail::compose($to, $subject, $body);

    expect($email->to())->toBe($to)
        ->and($email->subject())->toBe($subject)
        ->and($email->body())->toBe($body)
        ->and($email->attachments())->toBe([]);
});

it('keeps attachments in order', function () {
    $receipt = EmailAttachment::fromContent('recu.txt', 'text/plain', 'Total');
    $invoice = EmailAttachment::fromContent('facture.pdf', 'application/pdf', '%PDF');

    $email = OutboundEmail::compose(
        EmailAddress::fromString('ada@shoppy.test'),
        EmailSubject::fromString('Bienvenue'),
        EmailBody::fromText('Bonjour Ada'),
        [$receipt, $invoice],
    );

    expect($email->attachments())->toBe([$receipt, $invoice]);
});

it('rejects an attachment that is not a file', function () {
    OutboundEmail::compose(
        EmailAddress::fromString('ada@shoppy.test'),
        EmailSubject::fromString('Bienvenue'),
        EmailBody::fromText('Bonjour Ada'),
        ['recu.txt'],
    );
})->throws(InvalidEmailAttachment::class);
