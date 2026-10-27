<?php

declare(strict_types=1);

namespace App\Audit\Application\QueryHandler;

use App\Audit\Application\Query\ListAuditLogQuery;
use App\Audit\Application\Response\AuditEntryResponse;
use App\Audit\Application\Response\AuditLogListResponse;
use App\Audit\Domain\Exception\InvalidAuditPagination;
use App\Audit\Domain\Repository\AuditLogRepository;

final readonly class ListAuditLogQueryHandler
{
    public function __construct(private AuditLogRepository $auditLog)
    {
    }

    public function handle(ListAuditLogQuery $query): AuditLogListResponse
    {
        if ($query->page < 1 || $query->limit < 1 || $query->limit > ListAuditLogQuery::MAX_LIMIT) {
            throw new InvalidAuditPagination();
        }

        $offset = ($query->page - 1) * $query->limit;

        return new AuditLogListResponse(
            array_map(AuditEntryResponse::fromEntry(...), $this->auditLog->findPage($offset, $query->limit)),
            $query->page,
            $query->limit,
            $this->auditLog->countAll(),
        );
    }
}
