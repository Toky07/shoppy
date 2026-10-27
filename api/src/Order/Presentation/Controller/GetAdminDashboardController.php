<?php

declare(strict_types=1);

namespace App\Order\Presentation\Controller;

use App\Auth\Presentation\Security\AuthorizationAttributes;
use App\Order\Application\Query\GetAdminDashboardQuery;
use App\Order\Application\QueryHandler\GetAdminDashboardQueryHandler;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final readonly class GetAdminDashboardController
{
    public function __construct(
        private GetAdminDashboardQueryHandler $getDashboard,
    ) {
    }

    #[Route('/admin/dashboard', methods: ['GET'])]
    #[IsGranted(AuthorizationAttributes::ROLE_ADMIN)]
    public function __invoke(): JsonResponse
    {
        return new JsonResponse($this->getDashboard->handle(new GetAdminDashboardQuery())->toArray());
    }
}
