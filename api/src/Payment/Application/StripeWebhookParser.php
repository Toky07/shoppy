<?php

declare(strict_types=1);

namespace App\Payment\Application;

use App\Payment\Application\Response\StripeWebhookEvent;

interface StripeWebhookParser
{
    public function parse(string $payload, string $signature): StripeWebhookEvent;
}
