<?php

declare(strict_types=1);

namespace App\Tests\Doubles;

use App\Payment\Infrastructure\Gateway\Stripe\CreatedStripeSession;
use App\Payment\Infrastructure\Gateway\Stripe\RetrievedStripeSession;
use App\Payment\Infrastructure\Gateway\Stripe\StripeCheckoutClient;

final class FakeStripeCheckoutClient implements StripeCheckoutClient
{
    /** @var list<array{
     *     paymentId: string,
     *     orderId: string,
     *     amountCents: int,
     *     currency: string,
     *     successUrl: string,
     *     cancelUrl: string,
     *     userId: string,
     *     email: ?string
     * }>
     */
    public array $createdSessions = [];

    /** @var list<string> */
    public array $expiredSessionIds = [];

    /** @var array<string, int> */
    private array $sessionAmounts = [];

    public bool $sessionPaid = true;

    public ?int $sessionAmountOverride = null;

    public function createSession(
        string $paymentId,
        string $orderId,
        int $amountCents,
        string $currency,
        string $successUrl,
        string $cancelUrl,
        string $userId,
        ?string $email = null,
    ): CreatedStripeSession {
        $this->createdSessions[] = [
            'paymentId' => $paymentId,
            'orderId' => $orderId,
            'amountCents' => $amountCents,
            'currency' => $currency,
            'successUrl' => $successUrl,
            'cancelUrl' => $cancelUrl,
            'userId' => $userId,
            'email' => $email,
        ];
        $id = 'cs_test_'.$paymentId;
        $this->sessionAmounts[$id] = $amountCents;

        return new CreatedStripeSession($id, 'https://checkout.test/'.$id);
    }

    public function retrieveSession(string $sessionId): ?RetrievedStripeSession
    {
        if (!isset($this->sessionAmounts[$sessionId])) {
            return null;
        }

        return new RetrievedStripeSession(
            $sessionId,
            $this->sessionPaid,
            $this->sessionAmountOverride ?? $this->sessionAmounts[$sessionId],
        );
    }

    public function expireSession(string $sessionId): void
    {
        $this->expiredSessionIds[] = $sessionId;
    }
}
