import type { AdminDashboard } from '../domain/AdminDashboard'

export interface AdminDashboardRepository {
  get(): Promise<AdminDashboard>
}
