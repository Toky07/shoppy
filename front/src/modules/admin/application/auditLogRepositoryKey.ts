import type { InjectionKey } from 'vue'
import type { AuditLogRepository } from './AuditLogRepository'

export const auditLogRepositoryKey: InjectionKey<AuditLogRepository> = Symbol('auditLogRepository')
