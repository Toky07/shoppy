<?php

declare(strict_types=1);

use App\Audit\Domain\Exception\InvalidAuditId;
use App\Audit\Domain\ValueObject\AuditId;

it('rejects an invalid uuid', function () {
    AuditId::fromString('not-a-uuid');
})->throws(InvalidAuditId::class);

it('generates time-ordered ids so newest sorts first', function () {
    $older = AuditId::generate();
    usleep(2_000);
    $newer = AuditId::generate();

    expect($older->value())->toMatch('/^[0-9a-f]{8}-[0-9a-f]{4}-7[0-9a-f]{3}-[0-9a-f]{4}-[0-9a-f]{12}$/i')
        ->and($newer->value() <=> $older->value())->toBe(1);
});
