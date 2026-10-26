<?php

declare(strict_types=1);

namespace App\Shared\Presentation\Controller;

use App\Shared\Application\HealthProbe;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final readonly class HealthController
{
    public function __construct(private HealthProbe $health)
    {
    }

    #[Route('/health', methods: ['GET'])]
    public function __invoke(): JsonResponse
    {
        if (!$this->health->isHealthy()) {
            return $this->response(['status' => 'error'], JsonResponse::HTTP_SERVICE_UNAVAILABLE);
        }

        return $this->response(['status' => 'ok']);
    }

    /**
     * @param array{status: string} $payload
     */
    private function response(array $payload, int $status = JsonResponse::HTTP_OK): JsonResponse
    {
        $response = new JsonResponse($payload, $status);
        $response->headers->set('Cache-Control', 'no-store');

        return $response;
    }
}
