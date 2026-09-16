<?php

declare(strict_types=1);

use App\Payment\Application\PaymentGatewayRegistry;
use App\Payment\Domain\Exception\UnknownPaymentProvider;
use App\Payment\Infrastructure\Gateway\LocalPaymentGateway;
use App\Tests\Doubles\FakePaymentGateway;

it('returns the default payment gateway', function () {
    $local = new LocalPaymentGateway();
    $registry = new PaymentGatewayRegistry([$local, new FakePaymentGateway()], 'local');

    expect($registry->default())->toBe($local)
        ->and($registry->defaultName())->toBe('local');
});

it('returns a registered gateway by name', function () {
    $stripe = new FakePaymentGateway(name: 'stripe');
    $registry = new PaymentGatewayRegistry([new LocalPaymentGateway(), $stripe], 'local');

    expect($registry->get('stripe'))->toBe($stripe)
        ->and($registry->get('local'))->toBeInstanceOf(LocalPaymentGateway::class);
});

it('rejects an unknown payment provider', function () {
    $registry = new PaymentGatewayRegistry([new LocalPaymentGateway()], 'local');

    $registry->get('paypal');
})->throws(UnknownPaymentProvider::class);
