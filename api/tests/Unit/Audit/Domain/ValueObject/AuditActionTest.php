<?php

declare(strict_types=1);

use App\Audit\Domain\Exception\InvalidAuditAction;
use App\Audit\Domain\ValueObject\AuditAction;

it('exposes the tracked admin actions', function () {
    expect(AuditAction::roleChanged()->value())->toBe('role_changed')
        ->and(AuditAction::priceChanged()->value())->toBe('price_changed')
        ->and(AuditAction::stockChanged()->value())->toBe('stock_changed')
        ->and(AuditAction::paymentChanged()->value())->toBe('payment_changed');
});

it('rejects an unknown action', function () {
    AuditAction::fromString('deleted');
})->throws(InvalidAuditAction::class);
