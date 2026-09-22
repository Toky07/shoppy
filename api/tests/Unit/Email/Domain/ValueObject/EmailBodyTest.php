<?php

declare(strict_types=1);

use App\Email\Domain\Exception\InvalidEmailBody;
use App\Email\Domain\ValueObject\EmailBody;

it('keeps the text part and an optional html part', function () {
    $body = EmailBody::fromText('Bonjour Ada', '<p>Bonjour Ada</p>');

    expect($body->text())->toBe('Bonjour Ada')
        ->and($body->html())->toBe('<p>Bonjour Ada</p>');
});

it('treats a blank html part as absent', function () {
    $body = EmailBody::fromText('Bonjour Ada', '   ');

    expect($body->html())->toBeNull();
});

it('rejects an empty text part', function () {
    EmailBody::fromText('   ');
})->throws(InvalidEmailBody::class);
