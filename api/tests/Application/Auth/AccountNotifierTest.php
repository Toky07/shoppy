<?php

declare(strict_types=1);

use App\Auth\Application\AccountNotifier;
use App\Tests\Doubles\RecordingEventBus;

it('puts account tokens in the url fragment instead of the query string', function (string $send, string $path) {
    $events = new RecordingEventBus();
    $notifier = new AccountNotifier($events, 'http://localhost:5173/');
    $notifier->{$send}('ada@shoppy.test', 'deadbeefcafebabe');

    $text = $events->published[0]->body->text();

    expect($text)->toContain('http://localhost:5173'.$path.'#token=deadbeefcafebabe')
        ->and($text)->not->toContain('?token=');
})->with([
    ['sendPasswordReset', '/reset-password'],
    ['sendEmailVerification', '/verify-email'],
    ['sendEmailChange', '/confirm-email'],
]);
