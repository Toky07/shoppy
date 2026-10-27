import type { HttpClient } from '@/shared/http/HttpClient'
import { InvalidResponseError } from '@/shared/http/InvalidResponseError'
import { isRecord } from '@/shared/types/isRecord'
import type { AuditLogRepository } from '../application/AuditLogRepository'
import { AUDIT_ACTIONS, type AuditEntry, type AuditLogPage, type ListAuditLogQuery } from '../domain/AuditEntry'

export class AuditLogHttpRepository implements AuditLogRepository {
  constructor(private readonly http: HttpClient) {}

  async list(query: ListAuditLogQuery): Promise<AuditLogPage> {
    return mapAuditLogPage(await this.http.get('/admin/audit', query))
  }
}

function mapAuditLogPage(payload: unknown): AuditLogPage {
  if (
    !isRecord(payload) ||
    !Array.isArray(payload.items) ||
    typeof payload.page !== 'number' ||
    typeof payload.limit !== 'number' ||
    typeof payload.total !== 'number'
  ) {
    throw new InvalidResponseError('Invalid audit log payload.')
  }

  return {
    items: payload.items.map(mapAuditEntry),
    page: payload.page,
    limit: payload.limit,
    total: payload.total,
  }
}

function mapAuditEntry(payload: unknown): AuditEntry {
  if (
    !isRecord(payload) ||
    typeof payload.id !== 'string' ||
    typeof payload.action !== 'string' ||
    !AUDIT_ACTIONS.includes(payload.action as AuditEntry['action']) ||
    (payload.actorId !== null && typeof payload.actorId !== 'string') ||
    (payload.actorEmail !== null && typeof payload.actorEmail !== 'string') ||
    (payload.subjectType !== 'user' && payload.subjectType !== 'product' && payload.subjectType !== 'order') ||
    typeof payload.subjectId !== 'string' ||
    !isRecord(payload.metadata) ||
    typeof payload.createdAt !== 'string'
  ) {
    throw new InvalidResponseError('Invalid audit log payload.')
  }

  return {
    id: payload.id,
    action: payload.action as AuditEntry['action'],
    actorId: payload.actorId,
    actorEmail: payload.actorEmail,
    subjectType: payload.subjectType,
    subjectId: payload.subjectId,
    metadata: payload.metadata as AuditEntry['metadata'],
    createdAt: payload.createdAt,
  }
}
