import type { InjectionKey } from 'vue'
import type { AdminDashboardRepository } from './AdminDashboardRepository'

export const adminDashboardRepositoryKey: InjectionKey<AdminDashboardRepository> =
  Symbol('adminDashboardRepository')
