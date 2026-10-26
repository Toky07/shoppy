<?php

declare(strict_types=1);

use App\Media\Application\MediaDerivative;

it('names each derivative next to the original file', function () {
    expect(MediaDerivative::Thumb->pathFor('2026/09/tee.jpg'))->toBe('2026/09/tee.thumb.jpg')
        ->and(MediaDerivative::Card->pathFor('2026/09/tee.jpg'))->toBe('2026/09/tee.card.jpg')
        ->and(MediaDerivative::Detail->pathFor('2026/09/tee.jpg'))->toBe('2026/09/tee.detail.jpg')
        ->and(MediaDerivative::Thumb->maxWidth())->toBe(128)
        ->and(MediaDerivative::Card->maxWidth())->toBe(600)
        ->and(MediaDerivative::Detail->maxWidth())->toBe(1200);
});
