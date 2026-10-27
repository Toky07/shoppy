import type { AuditLogRepository } from '../application/AuditLogRepository'
import type { AuditEntry, AuditLogPage, ListAuditLogQuery } from '../domain/AuditEntry'

export class FakeAuditLogRepository implements AuditLogRepository {
  public entries: AuditEntry[]
  public listCount = 0
  public listError: Error | null = null

  constructor(entries: AuditEntry[] = []) {
    this.entries = entries.map((entry) => ({ ...entry, metadata: { ...entry.metadata } }))
  }

  async list(query: ListAuditLogQuery): Promise<AuditLogPage> {
    this.listCount += 1
    if (this.listError) {
      throw this.listError
    }

    const start = (query.page - 1) * query.limit

    return {
      items: this.entries.slice(start, start + query.limit).map((entry) => ({
        ...entry,
        metadata: { ...entry.metadata },
      })),
      page: query.page,
      limit: query.limit,
      total: this.entries.length,
    }
  }
}
