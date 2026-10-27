import type { AuditLogPage, ListAuditLogQuery } from '../domain/AuditEntry'

export interface AuditLogRepository {
  list(query: ListAuditLogQuery): Promise<AuditLogPage>
}
