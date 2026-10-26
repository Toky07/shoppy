<?php

declare(strict_types=1);

use App\Shared\Application\HealthProbe;
use App\Shared\Presentation\Controller\HealthController;

it('returns service unavailable when the probe fails', function () {
    $probe = new class implements HealthProbe {
        public function isHealthy(): bool
        {
            return false;
        }
    };

    $response = (new HealthController($probe))();

    expect($response->getStatusCode())->toBe(503)
        ->and(json_decode((string) $response->getContent(), true))->toBe(['status' => 'error']);
});
