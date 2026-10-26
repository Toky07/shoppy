<?php

declare(strict_types=1);

use App\Shared\Application\RateLimitWindow;
use App\Shared\Domain\Exception\RateLimited;

it('records hits until the limit is reached', function () {
    $window = RateLimitWindow::parse('', 0)->record(10, 2)->record(11, 2);

    expect($window->toString())->toBe('10,11')
        ->and(fn () => $window->record(12, 2))->toThrow(RateLimited::class);
});

it('drops hits that fall outside the window', function () {
    $window = RateLimitWindow::parse('10,40,50', 30);

    expect($window->toString())->toBe('40,50');
});
