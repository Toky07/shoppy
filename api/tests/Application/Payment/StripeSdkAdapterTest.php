<?php

declare(strict_types=1);

use App\Payment\Domain\Exception\InvalidPaymentWebhook;
use App\Payment\Domain\Exception\PaymentProviderNotConfigured;
use App\Payment\Infrastructure\Gateway\Stripe\StripeSdkCheckoutClient;
use App\Payment\Infrastructure\Gateway\Stripe\StripeSdkWebhookParser;

function signedStripePayload(string $payload, string $secret, int $timestamp): string
{
    $signature = hash_hmac('sha256', $timestamp.'.'.$payload, $secret);

    return sprintf('t=%d,v1=%s', $timestamp, $signature);
}

it('parses a signed checkout.session.completed webhook', function () {
    $secret = 'whsec_test_secret';
    $payload = json_encode([
        'id' => 'evt_test',
        'object' => 'event',
        'type' => 'checkout.session.completed',
        'data' => [
            'object' => [
                'id' => 'cs_test_123',
                'object' => 'checkout.session',
            ],
        ],
    ], JSON_THROW_ON_ERROR);
    $timestamp = time();

    $event = (new StripeSdkWebhookParser($secret))->parse(
        $payload,
        signedStripePayload($payload, $secret, $timestamp),
    );

    expect($event->type)->toBe('checkout.session.completed')
        ->and($event->sessionId)->toBe('cs_test_123');
});

it('rejects a stripe webhook with an invalid signature', function () {
    (new StripeSdkWebhookParser('whsec_test_secret'))->parse(
        '{"type":"checkout.session.completed"}',
        't=1,v1=deadbeef',
    );
})->throws(InvalidPaymentWebhook::class);

it('does not start a stripe checkout without a secret key', function () {
    (new StripeSdkCheckoutClient(''))->createSession(
        'dddddddd-dddd-4ddd-8ddd-dddddddddddd',
        'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
        3998,
        'eur',
        'http://localhost:5173/success',
        'http://localhost:5173/cancel',
    );
})->throws(PaymentProviderNotConfigured::class);
