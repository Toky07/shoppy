<?php

declare(strict_types=1);

namespace App\Audit\Presentation\Controller;

use App\Audit\Application\Query\ListAuditLogQuery;
use App\Audit\Application\QueryHandler\ListAuditLogQueryHandler;
use App\Auth\Presentation\Security\AuthorizationAttributes;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final readonly class ListAuditLogController
{
    public function __construct(
        private ListAuditLogQueryHandler $listAuditLog,
    ) {
    }

    #[Route('/admin/audit', methods: ['GET'])]
    #[IsGranted(AuthorizationAttributes::ROLE_ADMIN)]
    public function __invoke(Request $request): JsonResponse
    {
        return new JsonResponse($this->listAuditLog->handle(new ListAuditLogQuery(
            page: $request->query->getInt('page', ListAuditLogQuery::DEFAULT_PAGE),
            limit: $request->query->getInt('limit', ListAuditLogQuery::DEFAULT_LIMIT),
        ))->toArray());
    }
}
