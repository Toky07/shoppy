<?php

declare(strict_types=1);

namespace App\Tests\Doubles;

use App\Payment\Infrastructure\Gateway\Stripe\CreatedStripeSession;
use App\Payment\Infrastructure\Gateway\Stripe\StripeCheckoutClient;

final class FakeStripeCheckoutClient implements StripeCheckoutClient
{
    /** @var list<string> */
    public array $expiredSessionIds = [];

    public function createSession(
        string $paymentId,
        string $orderId,
        int $amountCents,
        string $currency,
        string $successUrl,
        string $cancelUrl,
    ): CreatedStripeSession {
        $id = 'cs_test_'.$paymentId;

        return new CreatedStripeSession($id, 'https://checkout.test/'.$id);
    }

    public function expireSession(string $sessionId): void
    {
        $this->expiredSessionIds[] = $sessionId;
    }
}
